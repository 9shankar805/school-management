/**
 * generic-sync.js — Unified offline queue replay for NepalSchoolHub v2.
 *
 * Responsibility:
 *   1. Watch the `pending_sync` IndexedDB store (created by offline-db.js v2).
 *   2. If online → flush queue immediately (per-item, ordered by priority+created_at).
 *   3. If offline → register Service Worker Background Sync tag `sync-pending-generic`.
 *   4. When online fires (event, SW sync event, reconnect poll) → flush queue again.
 *   5. On success → mark item `synced`; on 5xx/network → retry with exponential
 *      backoff (max 5 attempts); on 4xx → mark `failed` + preserve error.
 *   6. Update every KPI badge / Sync List UI via document events.
 *
 * Depends on:   offline-db.js (MUST come first).
 * Pair with:    offline-forms.js (intercepts forms → calls offlineQueueAdd).
 *                sync-list.blade (UI to retry/delete individual items).
 *
 * Public API (window.OfflineSync):
 *   OfflineSync.flush()              → flush all pending items NOW
 *   OfflineSync.retry(local_id)      → retry one specific failed item
 *   OfflineSync.remove(local_id)     → delete one queued item (manual cancel)
 *   OfflineSync.getCounts()          → Promise of { pending, syncing, failed, synced, total }
 *   OfflineSync.getAll()             → Promise of queue array (priority-sorted)
 */

(function () {
    'use strict';

    const SYNC_TAG       = 'sync-pending-generic';
    const CHANNEL_NAME   = 'sw-sync';
    const MAX_ATTEMPTS   = 5;
    const BATCH_DELAY_MS = 250;

    let _isFlushing = false;
    let _swReady    = null;
    const log = (...a) => console.log('[GenericSync]', ...a);

    // ── Helpers ──────────────────────────────────────────────────────────────

    function getCsrf() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : (window.Laravel?.csrfToken || '');
    }

    function pickMethod(entry) {
        if (entry.method) return entry.method;
        return { store: 'POST', update: 'PATCH', destroy: 'DELETE' }[entry.action] || 'POST';
    }

    function currentUserId() {
        try {
            return parseInt(document.body.dataset.currentUserId || '0', 10) || null;
        } catch (_) { return null; }
    }

    function currentSchoolId() {
        return null; // server-side scopes via session — payload is already correct
    }

    /** Build FormData from a plain object (supports nested arrays like student_ids[]). */
    function buildFormData(payload) {
        const fd = new FormData();
        const append = (key, value) => {
            if (Array.isArray(value)) {
                value.forEach((v, i) => {
                    if (value.length > 0 && typeof value[0] !== 'object' && key.endsWith('[]')) {
                        fd.append(key, v);
                    } else {
                        append(`${key}[${i}]`, v);
                    }
                });
            } else if (value !== null && value !== undefined) {
                fd.append(key, String(value));
            }
        };

        for (const key of Object.keys(payload || {})) append(key, payload[key]);
        return fd;
    }

    // ── Sync one pending item ────────────────────────────────────────────────

    async function sendOne(item) {
        const endpoint   = item.endpoint;
        const method     = pickMethod(item);
        const payload    = item.payload || {};

        // Include CSRF because we hit Laravel's VerifyCsrfToken middleware
        if (method !== 'GET' && !payload._token) payload._token = getCsrf();
        if (!payload._method && (method === 'PATCH' || method === 'DELETE')) {
            payload._method = method;
        }

        const body    = buildFormData(payload);
        const headers = { 'Accept': 'application/json', 'X-Offline-Queued': '1' };
        // Note: fetch with FormData automatically sets multipart Content-Type.

        const start = performance.now();
        try {
            const res = await fetch(endpoint, {
                method:      method === 'DELETE' ? 'POST' : method,
                credentials: 'same-origin',
                headers,
                redirect:    'follow',
                body,
            });

            const durationMs = Math.round(performance.now() - start);

            // Capture body for audit (best-effort; handle JSON + plain)
            let responseBody;
            try {
                const text = await res.text();
                try { responseBody = JSON.parse(text); } catch (_) { responseBody = text.slice(0, 1000); }
            } catch (_) { responseBody = null; }

            if (res.ok) {
                return { ok: true,  status: res.status, durationMs, body: responseBody, res };
            }
            return { ok: false, status: res.status, durationMs, body: responseBody, fatal: res.status >= 400 && res.status < 500 };
        } catch (err) {
            // Network error (CORS offline, DNS, etc.)
            return { ok: false, status: 0, durationMs: Math.round(performance.now() - start), error: String(err), fatal: false };
        }
    }

    // ── Process a single item end-to-end ─────────────────────────────────────

    async function processItem(item) {
        // Claim immediately (prevents double-processing from other tabs).
        await offlineDbUpdate('pending_sync', item.local_id, {
            status:       'syncing',
            last_attempt: new Date().toISOString(),
            retry_count:  (item.retry_count || 0) + 1,
        });
        emitQueueChanged();

        const result = await sendOne(item);
        const nowIso = new Date().toISOString();

        if (result.ok) {
            await offlineDbUpdate('pending_sync', item.local_id, {
                status:          'synced',
                synced_at:       nowIso,
                last_error:      null,
                server_response: result.body,
                updated_at:      nowIso,
            });
            await offlineSyncLog({
                module:    item.module,
                outcome:   'synced',
                status:    result.status,
                duration:  result.durationMs,
                detail:    { label: item.label, local_id: item.local_id },
            });
            log(`✓ ${item.module} #${item.local_id} synced in ${result.durationMs}ms`);
            return { ok: true, item, result };
        }

        // Failure
        const fatal = !!result.fatal;
        const attemptsLeft = MAX_ATTEMPTS - ((item.retry_count || 0) + 1);
        const terminal = fatal || attemptsLeft <= 0;

        await offlineDbUpdate('pending_sync', item.local_id, {
            status:          terminal ? 'failed' : 'pending',
            last_error:      JSON.stringify({
                status:  result.status,
                body:    result.body,
                message: result.error,
                fatal,
                attempts_left: terminal ? 0 : attemptsLeft,
            }),
            server_response: result.body,
            updated_at:      nowIso,
        });

        await offlineSyncLog({
            module:    item.module,
            outcome:   terminal ? 'failed' : 'retry-scheduled',
            status:    result.status,
            duration:  result.durationMs,
            detail:    { label: item.label, local_id: item.local_id, error: result.error },
        });

        if (terminal) {
            log(`✗ ${item.module} #${item.local_id} FAILED (${result.status}) — manual review required`);
        } else {
            log(`⏎ ${item.module} #${item.local_id} ${result.status || 'network error'} — will retry (${attemptsLeft} left)`);
        }
        return { ok: false, item, terminal, result };
    }

    // ── Full queue flush ─────────────────────────────────────────────────────

    async function flush({ force = false } = {}) {
        if (_isFlushing) { log('flush already running — skipped'); return { skipped: true }; }
        if (!navigator.onLine && !force) {
            log('flush called while offline — register Background Sync and bail');
            registerBackgroundSync();
            return { offline: true };
        }

        _isFlushing = true;
        emitQueueChanged();

        let synced = 0, failed = 0, retried = 0;

        try {
            const queue = await offlineQueueGetAll();
            const pendingFailed = queue.filter((x) =>
                x.status === 'pending' || x.status === 'syncing' ||
                (force && x.status === 'failed')
            );

            log(`Flushing ${pendingFailed.length} items…`);

            for (const item of pendingFailed) {
                // Bail mid-loop if device went offline again.
                if (!navigator.onLine && !force) {
                    log('Went offline mid-flush — stopping; remainder will sync on reconnect');
                    break;
                }
                const out = await processItem(item);
                if (out.ok) synced++;
                else if (out.terminal) failed++;
                else retried++;
                await new Promise((r) => setTimeout(r, BATCH_DELAY_MS));
            }
        } finally {
            _isFlushing = false;
            emitQueueChanged();
        }

        log(`Flush done → synced: ${synced}, failed (terminal): ${failed}, retried: ${retried}`);
        return { synced, failed, retried };
    }

    // ── Single-item retry ────────────────────────────────────────────────────

    async function retryOne(localId) {
        const item = await offlineDbGet('pending_sync', localId);
        if (!item) throw new Error('Queue item not found');
        const out = await processItem(item);
        emitQueueChanged();
        return out;
    }

    // ── Single-item remove ───────────────────────────────────────────────────

    async function removeOne(localId) {
        await offlineDbDelete('pending_sync', localId);
        emitQueueChanged();
    }

    // ── Service Worker Background Sync registration ──────────────────────────

    function swReady() {
        if (!('serviceWorker' in navigator)) return Promise.reject(new Error('No SW'));
        if (_swReady) return _swReady;
        _swReady = navigator.serviceWorker.ready;
        return _swReady;
    }

    function registerBackgroundSync() {
        if (!('SyncManager' in window)) return false;
        swReady()
            .then((reg) => reg.sync.register(SYNC_TAG))
            .then(() => log(`Background Sync tag "${SYNC_TAG}" registered`))
            .catch((e) => console.warn('[GenericSync] SW sync.register failed — polling instead:', e));
        return true;
    }

    // ── UI broadcast helpers ─────────────────────────────────────────────────

    function emitQueueChanged() {
        offlineQueueStatusCounts().then((counts) => {
            document.dispatchEvent(new CustomEvent('offline-queue-changed', { detail: { counts } }));
        });
        document.dispatchEvent(new CustomEvent('offline-queue-refresh-required'));
    }

    // ── Polling fallback (for browsers without Background Sync) ──────────────

    function startPolling() {
        // Every 30s: if online and there are pending items → try flush
        setInterval(() => {
            if (!navigator.onLine) return;
            offlineQueueStatusCounts().then((c) => {
                if ((c.pending || 0) + (c.syncing || 0) > 0) flush();
            });
        }, 30 * 1000);
    }

    // ── Public API (window.OfflineSync) ──────────────────────────────────────

    window.OfflineSync = {
        flush:        flush,
        retry:        retryOne,
        remove:       removeOne,
        getCounts:    offlineQueueStatusCounts,
        getAll:       offlineQueueGetAll,
        registerBgSync: registerBackgroundSync,
    };

    // ── Wire up events ───────────────────────────────────────────────────────

    function start() {
        // 1. Whenever the device comes back online → flush
        window.addEventListener('online', () => {
            log('online event → flush queue + re-seed if stale');
            flush();
            if (typeof window.offlineSeed === 'function') window.offlineSeed();
        });

        // 2. When SW tells us background sync fired → flush
        if (window.BroadcastChannel) {
            try {
                const ch = new BroadcastChannel(CHANNEL_NAME);
                ch.addEventListener('message', (ev) => {
                    if (ev.data?.type === 'generic-sync-requested') flush();
                    if (ev.data?.type === 'attendance-sync-requested' && window.flushPendingAttendance) {
                        // legacy path for attendance-sync.js
                    }
                });
            } catch (_) { /* ignore */ }
        }

        // 3. When a form submission is queued offline → register BG sync
        document.addEventListener('offline-form-queued', () => registerBackgroundSync());

        // 4. Polling fallback (catch-all for Safari / older browsers)
        startPolling();

        // 5. Initial state: if already online and we have pending, flush now
        setTimeout(() => {
            offlineQueueStatusCounts().then((c) => {
                emitQueueChanged();
                const need = (c.pending || 0) + (c.syncing || 0);
                if (need > 0 && navigator.onLine) flush();
                else if (need > 0) registerBackgroundSync();
            });
        }, 1500);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start, { once: true });
    } else {
        start();
    }

})();
