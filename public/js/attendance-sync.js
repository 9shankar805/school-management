/**
 * attendance-sync.js — Offline attendance queue + background sync for NepalSchoolHub.
 *
 * Flow:
 *   1. Teacher submits attendance form (online or offline).
 *   2. queueAttendance() writes records to IndexedDB (pending_attendance store).
 *   3. If online  → flushPendingAttendance() POSTs immediately to the server.
 *   4. If offline → registers Background Sync tag 'sync-attendance' with the SW.
 *   5. When device comes back online the SW fires the 'sync' event, broadcasts
 *      via BroadcastChannel('sw-sync'), and this module flushes the queue.
 *
 * Server endpoint: POST /attendances/store  (existing web route, CSRF-protected)
 * Payload mirrors the HTML form: student_ids[], status[<id>], session_id,
 * class_id, section_id, course_id — exactly what AttendanceController@store expects.
 *
 * Depends on: offline-db.js (must be loaded first)
 */

(function () {
    'use strict';

    const ATTENDANCE_STORE   = 'pending_attendance';
    const SYNC_CHANNEL_NAME  = 'sw-sync';
    const SYNC_TAG           = 'sync-attendance';
    const STORE_ENDPOINT     = '/attendances/store';

    // ── Helpers ───────────────────────────────────────────────────────────────

    function log(msg, ...args) { console.log('[AttendanceSync]', msg, ...args); }

    function getCsrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    }

    function pendingCount() {
        return offlineDbCount(ATTENDANCE_STORE, 'by_synced', 0);
    }

    // Update the pending-count badge in the offline banner (if present)
    function updateBadge() {
        pendingCount().then((n) => {
            const badge = document.getElementById('offline-pending-count');
            if (badge) badge.textContent = n;

            const wrapper = document.getElementById('offline-pending-wrapper');
            if (wrapper) wrapper.classList.toggle('hidden', n === 0);
        });
    }

    // ── Queue ─────────────────────────────────────────────────────────────────

    /**
     * Queue an attendance batch into IndexedDB.
     *
     * @param {object} payload  — matches the form fields:
     *   {
     *     session_id  : number,
     *     class_id    : number,
     *     section_id  : number,
     *     course_id   : number,
     *     school_id   : number,
     *     student_ids : number[],
     *     status      : { [student_id]: 'on'|'off' }
     *   }
     * @returns {Promise<IDBValidKey>}  local_id of the queued record
     */
    async function queueAttendance(payload) {
        const record = Object.assign({}, payload, {
            synced:     0,
            queued_at:  Date.now(),
            error:      null,
        });

        const localId = await offlineDbAdd(ATTENDANCE_STORE, record);
        log('Queued attendance batch, local_id =', localId);
        updateBadge();
        return localId;
    }

    // ── Flush ─────────────────────────────────────────────────────────────────

    /**
     * Read all un-synced records from IndexedDB and POST them to the server.
     * Marks each record synced=1 on success, or stores the error on failure.
     */
    async function flushPendingAttendance() {
        if (!navigator.onLine) {
            log('Still offline — flush deferred');
            return;
        }

        const pending = await offlineDbGetByIndex(ATTENDANCE_STORE, 'by_synced', 0);

        if (!pending.length) {
            log('Nothing to flush');
            return;
        }

        log(`Flushing ${pending.length} pending attendance record(s)…`);

        for (const record of pending) {
            try {
                // Rebuild FormData that matches the Blade form / AttendanceController@store
                const body = new FormData();
                body.append('_token',     getCsrf());
                body.append('session_id', record.session_id);
                body.append('class_id',   record.class_id);
                body.append('section_id', record.section_id ?? 0);
                body.append('course_id',  record.course_id  ?? 0);

                // student_ids[] and status[id]
                (record.student_ids || []).forEach((sid) => {
                    body.append('student_ids[]', sid);
                    const st = record.status?.[sid] ?? 'off';
                    body.append(`status[${sid}]`, st);
                });

                const res = await fetch(STORE_ENDPOINT, {
                    method:      'POST',
                    credentials: 'same-origin',
                    body,
                });

                if (res.ok || res.redirected) {
                    // Mark synced
                    await offlineDbUpdate(ATTENDANCE_STORE, record.local_id, {
                        synced:    1,
                        synced_at: Date.now(),
                        error:     null,
                    });

                    log(`Synced local_id=${record.local_id} ✓`);

                    // Write to sync log
                    await offlineDbAdd('sync_log', {
                        type:      'attendance',
                        local_id:  record.local_id,
                        synced_at: Date.now(),
                        status:    'success',
                    });
                } else {
                    const text = await res.text();
                    throw new Error(`HTTP ${res.status}: ${text.slice(0, 200)}`);
                }
            } catch (err) {
                log(`Failed to sync local_id=${record.local_id}:`, err.message);

                await offlineDbUpdate(ATTENDANCE_STORE, record.local_id, {
                    error:      err.message,
                    error_at:   Date.now(),
                });
            }
        }

        updateBadge();

        // Dispatch event so the UI can show a success toast
        const synced = pending.length;
        document.dispatchEvent(new CustomEvent('attendance-synced', {
            detail: { count: synced },
        }));
    }

    // ── Service Worker BroadcastChannel listener ─────────────────────────────

    if ('BroadcastChannel' in window) {
        const channel = new BroadcastChannel(SYNC_CHANNEL_NAME);
        channel.addEventListener('message', (e) => {
            if (e.data?.type === 'attendance-sync-requested') {
                log('SW requested flush via BroadcastChannel');
                flushPendingAttendance();
            }
        });
    }

    // ── Online event ──────────────────────────────────────────────────────────

    window.addEventListener('online', () => {
        log('Network restored — flushing queue');
        flushPendingAttendance();
    });

    // ── Register Background Sync with Service Worker ──────────────────────────

    async function registerBackgroundSync() {
        if (!('serviceWorker' in navigator) || !('SyncManager' in window)) return;
        try {
            const reg = await navigator.serviceWorker.ready;
            await reg.sync.register(SYNC_TAG);
            log('Background sync tag registered:', SYNC_TAG);
        } catch (err) {
            log('Background sync registration failed (will rely on online event):', err.message);
        }
    }

    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Submit attendance — online or offline.
     *
     * Replaces the native form submit. Called from the attendance form interceptor.
     *
     * @param {object} payload  — see queueAttendance() above
     * @returns {Promise<{queued: boolean, synced: boolean}>}
     */
    window.submitAttendance = async function (payload) {
        await queueAttendance(payload);

        if (navigator.onLine) {
            await flushPendingAttendance();
            return { queued: true, synced: true };
        } else {
            await registerBackgroundSync();
            return { queued: true, synced: false };
        }
    };

    /** Manually trigger a flush (called from the UI "Sync Now" button). */
    window.flushAttendanceSync = flushPendingAttendance;

    /** Expose pending count for UI. */
    window.attendancePendingCount = pendingCount;

    // ── Init: update badge on load and flush any leftovers ───────────────────

    document.addEventListener('DOMContentLoaded', () => {
        updateBadge();

        // Flush any records that were queued in a previous offline session
        if (navigator.onLine) {
            pendingCount().then((n) => {
                if (n > 0) {
                    log(`Found ${n} unsynced record(s) from previous session — flushing`);
                    flushPendingAttendance();
                }
            });
        }
    });

}());
