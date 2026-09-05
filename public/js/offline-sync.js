/**
 * offline-sync.js — Master sync engine for NepalSchoolHub offline-first PWA.
 *
 * Reads the pending_sync queue from IndexedDB (written by offline-forms.js and
 * attendance-sync.js) and replays each queued operation to the server in
 * priority order when the device comes back online.
 *
 * Flow:
 *   1. Device comes online  (window 'online' event or manual "Sync Now" click).
 *   2. offlineSyncFlush()   reads all pending / failed records.
 *   3. For each record:
 *       a. Mark status → 'syncing'.
 *       b. POST to record.endpoint with record.payload (FormData + CSRF).
 *       c. Success → mark 'synced', write to sync_log, strip optimistic
 *          cache record (replace offline-id with real server id if returned).
 *       d. Failure → mark 'failed', store error, increment retry_count.
 *          Retries up to MAX_RETRIES (3) before giving up.
 *   4. Update the banner badge count.
 *   5. Emit 'offline-sync-complete' CustomEvent with a results summary.
 *
 * Background Sync (via Service Worker sync tag 'sync-generic'):
 *   When the SW fires the sync event it broadcasts on BroadcastChannel('sw-sync')
 *   and this module's listener calls offlineSyncFlush().
 *
 * Depends on: offline-db.js (must be loaded first)
 * Load order: offline-db.js → offline-seed.js → attendance-sync.js → offline-forms.js → offline-sync.js
 */

(function () {
    'use strict';

    const MAX_RETRIES       = 3;
    const SYNC_TAG          = 'sync-generic';
    const CHANNEL_NAME      = 'sw-sync';

    function log(msg, ...a)  { console.log('[OfflineSync]',  msg, ...a); }
    function warn(msg, ...a) { console.warn('[OfflineSync]',  msg, ...a); }

    function getCsrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    }

    // ── Badge update ──────────────────────────────────────────────────────────

    async function updateBadge() {
        if (typeof offlineQueuePendingCount !== 'function') return;
        const n = await offlineQueuePendingCount();

        // Banner count span
        const span = document.getElementById('offline-pending-count');
        if (span) span.textContent = n;

        // Badge wrapper (show/hide)
        const wrap = document.getElementById('offline-pending-wrapper');
        if (wrap) wrap.classList.toggle('d-none', n === 0);
        if (wrap) wrap.classList.toggle('hidden', n === 0);

        // Sync panel count (if panel is open)
        const panelCount = document.getElementById('sync-panel-pending-count');
        if (panelCount) panelCount.textContent = n;

        // Sidebar sync badge
        const sidebarBadge = document.getElementById('offline-sidebar-badge');
        if (sidebarBadge) {
            sidebarBadge.textContent = n > 99 ? '99+' : n;
            sidebarBadge.classList.toggle('hidden', n === 0);
        }
    }

    // Expose globally so offline-forms.js can call it immediately after queuing
    window.offlineSyncUpdateBadge = updateBadge;

    // ── Replay a single queued record to the server ───────────────────────────

    async function replayRecord(record) {
        // Mark as syncing so UI can show a spinner
        await offlineDbUpdate('pending_sync', record.local_id, {
            status:       'syncing',
            last_attempt: new Date().toISOString(),
            updated_at:   new Date().toISOString(),
        });

        // Build FormData from stored payload
        const body = new FormData();
        body.append('_token', getCsrf());

        const payload = record.payload || {};
        for (const [key, val] of Object.entries(payload)) {
            if (Array.isArray(val)) {
                val.forEach((v) => body.append(key, v));
            } else if (val !== null && val !== undefined) {
                body.append(key, val);
            }
        }

        const method   = (record.method || 'POST').toUpperCase();
        const endpoint = record.endpoint;

        let res;
        try {
            res = await fetch(endpoint, {
                method,
                credentials: 'same-origin',
                body: method === 'GET' ? undefined : body,
            });
        } catch (networkErr) {
            throw new Error('Network error: ' + networkErr.message);
        }

        // Consider 200–299, 302 (redirect after store) as success
        if (!res.ok && !res.redirected && res.status !== 302) {
            const text = await res.text().catch(() => '');
            throw new Error(`HTTP ${res.status}: ${text.slice(0, 300)}`);
        }

        // Try to extract the server-assigned ID from a JSON response
        let serverId = null;
        const ct = res.headers.get('content-type') || '';
        if (ct.includes('application/json')) {
            try {
                const json = await res.clone().json();
                serverId = json.id || json.data?.id || null;
            } catch (_) { /* ignore */ }
        }

        return { ok: true, serverId };
    }

    // ── Promote offline cache record to a real one ────────────────────────────
    // When the server assigns a real ID we swap the optimistic 'offline-{localId}'
    // key with the real numeric ID so subsequent reads find the record.

    async function promoteOptimisticRecord(record, serverId) {
        const storeMap = {
            student:    'students_cache',
            syllabus:   'syllabi_cache',
            assignment: 'assignments_cache',
            marks:      'marks_cache',
            notice:     'notices_cache',
        };
        const store = storeMap[record.module];
        if (!store || !serverId) return;

        const offlineKey = 'offline-' + record.local_id;
        try {
            const optimistic = await offlineDbGet(store, offlineKey);
            if (optimistic) {
                await offlineDbDelete(store, offlineKey);
                await offlineDbPut(store, Object.assign({}, optimistic, {
                    id:       serverId,
                    _offline: false,
                    _local_id: null,
                }));
            }
        } catch (err) {
            warn('Could not promote optimistic record:', err);
        }
    }

    // ── Main flush function ───────────────────────────────────────────────────

    let _isFlushing = false;

    async function flush() {
        if (_isFlushing) {
            log('Already flushing — skipped duplicate call');
            return;
        }
        if (!navigator.onLine) {
            log('Still offline — flush deferred');
            return;
        }

        _isFlushing = true;
        let synced = 0, failed = 0;

        try {
            const queue = await offlineQueueGetAll();
            const pending = queue.filter((r) => r.status === 'pending' || r.status === 'failed');

            if (!pending.length) {
                log('Queue empty — nothing to sync');
                return;
            }

            log(`Flushing ${pending.length} queued operation(s)…`);

            for (const record of pending) {
                // Skip if max retries exhausted
                if ((record.retry_count || 0) >= MAX_RETRIES) {
                    warn(`Skipping local_id=${record.local_id} — max retries (${MAX_RETRIES}) reached`);
                    continue;
                }

                try {
                    const { ok, serverId } = await replayRecord(record);

                    if (ok) {
                        // Mark synced
                        await offlineDbUpdate('pending_sync', record.local_id, {
                            status:          'synced',
                            synced_at:       new Date().toISOString(),
                            server_response: { id: serverId },
                            last_error:      null,
                            updated_at:      new Date().toISOString(),
                        });

                        await offlineSyncLog({
                            module:   record.module,
                            local_id: record.local_id,
                            outcome:  'success',
                            label:    record.label,
                        });

                        await promoteOptimisticRecord(record, serverId);

                        log(`✓ Synced local_id=${record.local_id}  module=${record.module}`);
                        synced++;
                    }
                } catch (err) {
                    warn(`✗ Failed local_id=${record.local_id}  module=${record.module}:`, err.message);

                    await offlineDbUpdate('pending_sync', record.local_id, {
                        status:       'failed',
                        last_error:   err.message,
                        retry_count:  (record.retry_count || 0) + 1,
                        updated_at:   new Date().toISOString(),
                    });

                    await offlineSyncLog({
                        module:   record.module,
                        local_id: record.local_id,
                        outcome:  'failed',
                        error:    err.message,
                        label:    record.label,
                    });

                    failed++;
                }
            }
        } finally {
            _isFlushing = false;
            await updateBadge();
        }

        // Emit summary event
        document.dispatchEvent(new CustomEvent('offline-sync-complete', {
            detail: { synced, failed },
        }));

        // Show toast summary
        if (synced > 0 || failed > 0) {
            const msg = synced > 0
                ? `<i class="bi bi-check-circle-fill me-1"></i><strong>${synced}</strong> item${synced > 1 ? 's' : ''} synced successfully.`
                  + (failed > 0 ? ` <span class="text-rose-700">${failed} failed — will retry.</span>` : '')
                : `<i class="bi bi-exclamation-triangle-fill me-1"></i><strong>${failed}</strong> item${failed > 1 ? 's' : ''} failed to sync — will retry when online.`;
            const type = failed > 0 && synced === 0 ? 'error' : (failed > 0 ? 'warning' : 'success');
            if (typeof window.showToast === 'function') {
                window.showToast(msg, type, 6000);
            }
        }

        log(`Flush complete — synced=${synced} failed=${failed}`);
        return { synced, failed };
    }

    // ── Register background sync with SW ──────────────────────────────────────

    async function registerBackgroundSync() {
        if (!('serviceWorker' in navigator) || !('SyncManager' in window)) return;
        try {
            const reg = await navigator.serviceWorker.ready;
            await reg.sync.register(SYNC_TAG);
            log('Background sync tag registered:', SYNC_TAG);
        } catch (err) {
            log('Background sync unavailable (will rely on online event):', err.message);
        }
    }

    // ── BroadcastChannel listener (SW triggers flush) ─────────────────────────

    if ('BroadcastChannel' in window) {
        try {
            const ch = new BroadcastChannel(CHANNEL_NAME);
            ch.addEventListener('message', (e) => {
                if (e.data?.type === 'generic-sync-requested' || e.data?.type === 'sync-flush-requested') {
                    log('SW requested flush via BroadcastChannel');
                    flush();
                }
            });
        } catch (_) { /* ignore */ }
    }

    // ── Network event listeners ───────────────────────────────────────────────

    window.addEventListener('online', () => {
        log('Network restored — flushing pending queue');
        flush();
    });

    // ── Sync Panel UI ─────────────────────────────────────────────────────────
    // A small slide-out panel showing the queue state, opened by the banner button.

    function buildSyncPanel() {
        if (document.getElementById('offline-sync-panel')) return;

        const panel = document.createElement('div');
        panel.id    = 'offline-sync-panel';
        panel.setAttribute('aria-label', 'Offline sync queue panel');
        panel.style.cssText = [
            'position:fixed', 'bottom:1.5rem', 'right:1.5rem', 'z-index:9999',
            'width:340px', 'max-height:480px',
            'background:#fff', 'border:1px solid #e2e8f0',
            'border-radius:1rem', 'box-shadow:0 8px 32px rgba(0,0,0,.18)',
            'display:none', 'flex-direction:column', 'overflow:hidden',
            'font-family:inherit', 'font-size:0.8125rem',
        ].join(';');

        panel.innerHTML = `
        <div style="display:flex;align-items:center;justify-content:space-between;padding:0.875rem 1rem;border-bottom:1px solid #f1f5f9;background:#f8fafc">
            <span style="font-weight:700;color:#1e293b;display:flex;align-items:center;gap:0.5rem">
                <i class="bi bi-arrow-repeat" style="color:#6366f1"></i>
                Offline Queue
                <span id="sync-panel-pending-count" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;border-radius:9999px;padding:0.1rem 0.5rem;font-size:0.7rem;font-weight:700">0</span>
            </span>
            <div style="display:flex;gap:0.5rem;align-items:center">
                <button id="sync-panel-flush-btn"
                    style="background:#6366f1;color:#fff;border:none;border-radius:0.5rem;padding:0.3rem 0.75rem;font-size:0.75rem;font-weight:700;cursor:pointer">
                    <i class="bi bi-arrow-repeat"></i> Sync Now
                </button>
                <button onclick="document.getElementById('offline-sync-panel').style.display='none'"
                    style="background:transparent;border:none;color:#94a3b8;cursor:pointer;font-size:1rem;line-height:1;padding:0.25rem">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
        <div id="sync-panel-list" style="flex:1;overflow-y:auto;padding:0.5rem"></div>
        <div id="sync-panel-empty" style="padding:2rem 1rem;text-align:center;color:#94a3b8;display:none">
            <i class="bi bi-check2-circle" style="font-size:2rem;color:#10b981"></i>
            <p style="margin-top:0.5rem;font-size:0.8125rem">All caught up — nothing pending.</p>
        </div>`;

        document.body.appendChild(panel);

        document.getElementById('sync-panel-flush-btn').addEventListener('click', () => {
            flush();
            refreshSyncPanel();
        });
    }

    async function refreshSyncPanel() {
        const list  = document.getElementById('sync-panel-list');
        const empty = document.getElementById('sync-panel-empty');
        if (!list) return;

        const queue = await offlineQueueGetAll();
        const items = queue.filter((r) => r.status !== 'synced');

        list.innerHTML  = '';
        if (items.length === 0) {
            list.style.display  = 'none';
            if (empty) empty.style.display = 'block';
            return;
        }

        list.style.display  = '';
        if (empty) empty.style.display = 'none';

        const statusColors = {
            pending: { bg:'#fef9c3', text:'#92400e', border:'#fde68a', icon:'bi-clock-history' },
            syncing: { bg:'#eff6ff', text:'#1d4ed8', border:'#bfdbfe', icon:'bi-arrow-repeat'  },
            failed:  { bg:'#fff1f2', text:'#be123c', border:'#fecdd3', icon:'bi-exclamation-circle' },
            synced:  { bg:'#f0fdf4', text:'#166534', border:'#bbf7d0', icon:'bi-check-circle'  },
        };

        items.forEach((r) => {
            const c    = statusColors[r.status] || statusColors.pending;
            const mod  = window.OFFLINE_MODULES?.[r.module] || { icon: 'bi-file-earmark', label: r.module };
            const item = document.createElement('div');
            item.style.cssText = `display:flex;align-items:flex-start;gap:0.625rem;padding:0.625rem 0.75rem;border-radius:0.625rem;margin-bottom:0.375rem;background:${c.bg};border:1px solid ${c.border}`;
            item.innerHTML = `
                <i class="bi ${mod.icon}" style="color:${c.text};font-size:1rem;flex-shrink:0;margin-top:0.1rem"></i>
                <div style="flex:1;min-width:0">
                    <p style="margin:0;font-weight:600;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${r.label || r.module}</p>
                    <p style="margin:0;font-size:0.7rem;color:#64748b">${new Date(r.created_at).toLocaleString()}</p>
                    ${r.last_error ? `<p style="margin:0.2rem 0 0;font-size:0.7rem;color:${c.text}">${r.last_error.slice(0, 80)}</p>` : ''}
                </div>
                <span style="font-size:0.65rem;font-weight:700;background:${c.bg};color:${c.text};border:1px solid ${c.border};border-radius:9999px;padding:0.1rem 0.4rem;flex-shrink:0">${r.status}</span>`;
            list.appendChild(item);
        });
    }

    // ── Public API ────────────────────────────────────────────────────────────

    /** Trigger a manual sync flush. */
    window.offlineSyncFlush = flush;

    /** Open/refresh the sync panel. */
    window.openSyncPanel = async function () {
        buildSyncPanel();
        const panel = document.getElementById('offline-sync-panel');
        if (panel) panel.style.display = 'flex';
        await refreshSyncPanel();
        await updateBadge();
    };

    /** Close the sync panel. */
    window.closeSyncPanel = function () {
        const panel = document.getElementById('offline-sync-panel');
        if (panel) panel.style.display = 'none';
    };

    // ── Init on page load ─────────────────────────────────────────────────────

    async function init() {
        await updateBadge();

        // Flush any leftover items from a previous offline session
        if (navigator.onLine) {
            const count = await offlineQueuePendingCount();
            if (count > 0) {
                log(`Found ${count} pending item(s) from previous session — flushing`);
                flush();
            }
        } else {
            // Register SW background sync so flush fires when connectivity returns
            await registerBackgroundSync();
        }

        // Listen for sync-complete to refresh panel if open
        document.addEventListener('offline-sync-complete', async () => {
            await updateBadge();
            const panel = document.getElementById('offline-sync-panel');
            if (panel && panel.style.display !== 'none') await refreshSyncPanel();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }

}());
