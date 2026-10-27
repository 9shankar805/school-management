/**
 * offline-seed.js — Pre-cache ALL reference + live data into IndexedDB on login (v3)
 *
 * Fetches GET /api/offline/seed-data (same-origin cookie auth).
 *
 * V1: students_cache, classes_cache
 * V2: + staff, fee_structures, fee_types, subjects, hostels, transport, reference
 * V3: + syllabi_cache, assignments_cache, marks_cache, notices_cache, exams_cache
 *
 * Seed is skipped if data was refreshed within SEED_TTL_MS (6 hours).
 * Force refresh: window.offlineSeedForce()
 *
 * Depends on: offline-db.js (must be loaded first)
 */

(function () {
    'use strict';

    const SEED_TTL_MS   = 6 * 60 * 60 * 1000;  // 6 hours
    const SEED_KEY      = 'offline_seed_v5_at';  // v5 cache busting with full finance
    const SEED_ENDPOINT = '/api/offline/seed-data';
    const TOTAL_BATCHES = 22; // v5: 8 reference + 14 live-data stores (including finance)

    function log(msg, ...args) { console.log('[OfflineSeed v5]', msg, ...args); }

    function isStale() {
        const last = parseInt(localStorage.getItem(SEED_KEY) || '0', 10);
        return (Date.now() - last) > SEED_TTL_MS;
    }

    function markSeeded() {
        localStorage.setItem(SEED_KEY, String(Date.now()));
    }

    /** Public API */
    window.offlineSeedForce = () => seed(true);
    window.offlineSeed      = () => seed(false);

    // ── Progress helpers ──────────────────────────────────────────────────────

    let _progress = 0;

    function progress(done) {
        _progress = done;
        updateUiProgress(done, TOTAL_BATCHES);
        const pct = Math.round((done / TOTAL_BATCHES) * 100);
        const bar = document.getElementById('offline-seed-progress-bar');
        if (bar) bar.style.width = pct + '%';
    }

    function updateUiProgress(done, total) {
        document.dispatchEvent(new CustomEvent('offline-seed-progress', { detail: { done, total } }));
    }

    function broadcastSeedComplete(data) {
        document.dispatchEvent(new CustomEvent('offline-seed-complete', { detail: data }));
        if (window.BroadcastChannel) {
            try {
                const ch = new BroadcastChannel('sw-sync');
                ch.postMessage({ type: 'offline-seed-complete', payload: data });
                ch.close();
            } catch (_) { /* ignore */ }
        }
    }

    // ── Retry when reconnected ────────────────────────────────────────────────

    function scheduleOnReconnect() {
        const once = () => {
            if (navigator.onLine) {
                seed(false);
                window.removeEventListener('online', once);
            }
        };
        window.addEventListener('online', once);
    }

    // ── Seed ─────────────────────────────────────────────────────────────────

    async function seed(force) {
        if (!force && !isStale()) {
            log('Cache is fresh — skipping (call offlineSeedForce() to override)');
            return { skipped: true };
        }

        if (!navigator.onLine) {
            log('Offline — seed deferred until reconnect');
            scheduleOnReconnect();
            return { skipped: true, reason: 'offline' };
        }

        log('Seeding offline data (v3)…');
        showSeedingIndicator();

        // ── Fetch from server ─────────────────────────────────────────────
        let data;
        try {
            const res = await fetch(SEED_ENDPOINT, {
                method:      'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept':           'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            data = await res.json();
        } catch (err) {
            console.error('[OfflineSeed v3] Fetch failed:', err);
            hideSeedingIndicator();
            scheduleOnReconnect();
            return { skipped: true, reason: 'fetch_error', error: String(err) };
        }

        // ── Write to IndexedDB ────────────────────────────────────────────
        try {
            let done = 0;

            // 1. Classes + sections
            if (Array.isArray(data.classes) && data.classes.length) {
                await offlineDbBulkPut('classes_cache', data.classes);
                log(`✓ classes_cache (${data.classes.length})`);
            }
            progress(++done);

            // 2. Students (role-scoped)
            if (Array.isArray(data.students) && data.students.length) {
                await offlineDbBulkPut('students_cache', data.students);
                log(`✓ students_cache (${data.students.length})`);
            }
            progress(++done);

            // 3. Staff
            if (Array.isArray(data.staff) && data.staff.length) {
                await offlineDbBulkPut('staff_cache', data.staff);
                log(`✓ staff_cache (${data.staff.length})`);
            }
            progress(++done);

            // 4. Fee structures
            if (Array.isArray(data.fee_structures) && data.fee_structures.length) {
                await offlineDbBulkPut('fee_structures_cache', data.fee_structures);
                log(`✓ fee_structures_cache (${data.fee_structures.length})`);
            }
            progress(++done);

            // 5. Fee types
            if (Array.isArray(data.fee_types) && data.fee_types.length) {
                await offlineDbBulkPut('fee_types_cache', data.fee_types);
                log(`✓ fee_types_cache (${data.fee_types.length})`);
            }
            progress(++done);

            // 6. Subjects
            if (Array.isArray(data.subjects) && data.subjects.length) {
                await offlineDbBulkPut('subjects_cache', data.subjects);
                log(`✓ subjects_cache (${data.subjects.length})`);
            }
            progress(++done);

            // 7. Hostels
            if (Array.isArray(data.hostels) && data.hostels.length) {
                await offlineDbBulkPut('hostels_cache', data.hostels);
                log(`✓ hostels_cache (${data.hostels.length})`);
            }
            progress(++done);

            // 8. Transport routes
            if (Array.isArray(data.transport) && data.transport.length) {
                await offlineDbBulkPut('transport_cache', data.transport);
                log(`✓ transport_cache (${data.transport.length})`);
            }
            progress(++done);

            // 9. Syllabi  ── V3
            if (Array.isArray(data.syllabi) && data.syllabi.length) {
                await offlineDbBulkPut('syllabi_cache', data.syllabi);
                log(`✓ syllabi_cache (${data.syllabi.length})`);
            }
            progress(++done);

            // 10. Assignments  ── V3
            if (Array.isArray(data.assignments) && data.assignments.length) {
                await offlineDbBulkPut('assignments_cache', data.assignments);
                log(`✓ assignments_cache (${data.assignments.length})`);
            }
            progress(++done);

            // 11. Marks  ── V3
            if (Array.isArray(data.marks) && data.marks.length) {
                await offlineDbBulkPut('marks_cache', data.marks);
                log(`✓ marks_cache (${data.marks.length})`);
            }
            progress(++done);

            // 12. Notices  ── V3
            if (Array.isArray(data.notices) && data.notices.length) {
                await offlineDbBulkPut('notices_cache', data.notices);
                log(`✓ notices_cache (${data.notices.length})`);
            }
            progress(++done);

            // 13. Exams  ── V3
            if (Array.isArray(data.exams) && data.exams.length) {
                await offlineDbBulkPut('exams_cache', data.exams);
                log(`✓ exams_cache (${data.exams.length})`);
            }
            progress(++done);

            // 14. Homework  ── V4
            if (Array.isArray(data.homework) && data.homework.length) {
                await offlineDbBulkPut('homework_cache', data.homework);
                log(`✓ homework_cache (${data.homework.length})`);
            }
            progress(++done);

            // 15. Routines / Timetables  ── V4
            if (Array.isArray(data.routines) && data.routines.length) {
                await offlineDbBulkPut('routines_cache', data.routines);
                log(`✓ routines_cache (${data.routines.length})`);
            }
            progress(++done);

            // 16. Fee Invoices  ── V4
            if (Array.isArray(data.invoices) && data.invoices.length) {
                await offlineDbBulkPut('invoices_cache', data.invoices);
                log(`✓ invoices_cache (${data.invoices.length})`);
            }
            progress(++done);

            // 17. Visitors  ── V4
            if (Array.isArray(data.visitors) && data.visitors.length) {
                await offlineDbBulkPut('visitors_cache', data.visitors);
                log(`✓ visitors_cache (${data.visitors.length})`);
            }
            progress(++done);

            // 18. Leave Applications  ── V4
            if (Array.isArray(data.leave) && data.leave.length) {
                await offlineDbBulkPut('leave_cache', data.leave);
                log(`✓ leave_cache (${data.leave.length})`);
            }
            progress(++done);

            // 19. Feedbacks  ── V4
            if (Array.isArray(data.feedback) && data.feedback.length) {
                await offlineDbBulkPut('feedback_cache', data.feedback);
                log(`✓ feedback_cache (${data.feedback.length})`);
            }
            progress(++done);

            // 20. Expenses  ── V5
            if (Array.isArray(data.expenses) && data.expenses.length) {
                await offlineDbBulkPut('expenses_cache', data.expenses);
                log(`✓ expenses_cache (${data.expenses.length})`);
            }
            progress(++done);

            // 21. Income  ── V5
            if (Array.isArray(data.income) && data.income.length) {
                await offlineDbBulkPut('income_cache', data.income);
                log(`✓ income_cache (${data.income.length})`);
            }
            progress(++done);

            // 22. Payments / Receipts  ── V5
            if (Array.isArray(data.payments) && data.payments.length) {
                await offlineDbBulkPut('payments_cache', data.payments);
                log(`✓ payments_cache (${data.payments.length})`);
            }
            progress(++done);

            // Reference KV
            if (Array.isArray(data.reference)) {
                for (const kv of data.reference) {
                    await offlineRefPut(kv.key, kv.value);
                }
                log(`✓ reference_cache (${data.reference.length} keys)`);
            }

            markSeeded();
            hideSeedingIndicator();
            broadcastSeedComplete(data);

            log(`Seed complete ✓  schema_version=${data.schema_version}  session_id=${data.session_id}`);
            return { ok: true, schemaVersion: data.schema_version, sizes: data.sizes };

        } catch (err) {
            console.error('[OfflineSeed v3] IndexedDB write failed:', err);
            hideSeedingIndicator();
            return { ok: false, error: String(err) };
        }
    }

    // ── Small indicator in the offline banner ─────────────────────────────────

    function showSeedingIndicator() {
        const el = document.getElementById('offline-seeding-indicator');
        if (el) el.classList.remove('hidden');
    }

    function hideSeedingIndicator() {
        const el = document.getElementById('offline-seeding-indicator');
        if (el) el.classList.add('hidden');
    }

    // ── Auto-run on page load ─────────────────────────────────────────────────

    function autoSeed() {
        // Slight delay so IndexedDB is open and other scripts are ready
        setTimeout(() => seed(false), 2500);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', autoSeed, { once: true });
    } else {
        autoSeed();
    }

    // Re-seed when SW requests it via BroadcastChannel
    if (window.BroadcastChannel) {
        try {
            const ch = new BroadcastChannel('sw-sync');
            ch.addEventListener('message', (ev) => {
                if (ev.data?.type === 'seed-refresh-requested') seed(true);
            });
        } catch (_) { /* ignore */ }
    }

    // Re-seed after coming back online (max once per TTL)
    window.addEventListener('online', () => seed(false));

}());
