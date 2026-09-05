/**
 * offline-forms.js — Intercept HTML form submissions when offline and queue them in IndexedDB.
 *
 * Covered forms (by their action URL pattern):
 *   POST /student/create           → module: student
 *   POST /syllabus/create          → module: syllabus
 *   POST courses/assignments/create→ module: assignment
 *   POST /marks/store              → module: marks
 *   POST /notice/create            → module: notice
 *   POST /attendances/store        → module: attendance  (also handled by attendance-sync.js)
 *   POST /admissions               → module: admission
 *   POST /finance/payments         → module: fee_collection
 *   POST /finance/expenses         → module: expense
 *   POST /finance/income           → module: income
 *
 * When ONLINE  → form submits normally (we don't intercept it).
 * When OFFLINE → we:
 *   1. Capture all form fields as a plain object.
 *   2. Write to pending_sync store via offlineQueueAdd().
 *   3. Write an optimistic record to the matching *_cache store so the UI
 *      can show it immediately (with a "pending sync" badge).
 *   4. Show a success-style toast: "Saved offline — will sync when connected."
 *   5. Optionally redirect to the list page so the UX feels complete.
 *
 * Depends on: offline-db.js (must be loaded first)
 * Load order: offline-db.js → offline-seed.js → attendance-sync.js → offline-forms.js → offline-sync.js
 */

(function () {
    'use strict';

    // ── Form → module mapping ─────────────────────────────────────────────────
    // Each entry defines:
    //   pattern   : regex tested against form.action
    //   module    : key from OFFLINE_MODULES
    //   cacheStore: IndexedDB store to write the optimistic record to (or null)
    //   label     : fn(fields) → human-readable description
    //   redirect  : URL to navigate to after offline save (or null to stay)
    //   buildCache: fn(fields, localId) → record shape for the cache store

    const FORM_MAP = [
        {
            pattern:    /\/student\/create/,
            module:     'student',
            cacheStore: 'students_cache',
            label:      (f) => `Add Student: ${f.first_name || ''} ${f.last_name || ''}`.trim(),
            redirect:   '/students/view/list',
            buildCache: (f, localId) => ({
                student_id:   'offline-' + localId,
                first_name:   f.first_name  || '',
                last_name:    f.last_name   || '',
                class_id:     Number(f.class_id)   || 0,
                section_id:   Number(f.section_id) || 0,
                roll_no:      f.roll_no || null,
                admission_no: f.admission_no || null,
                photo:        null,
                _offline:     true,
                _local_id:    localId,
            }),
        },
        {
            pattern:    /\/syllabus\/create/,
            module:     'syllabus',
            cacheStore: 'syllabi_cache',
            label:      (f) => `Syllabus: ${f.title || f.topic || 'New Topic'}`,
            redirect:   '/syllabus/index',
            buildCache: (f, localId) => ({
                id:         'offline-' + localId,
                title:      f.title    || f.topic  || '',
                class_id:   Number(f.class_id)    || 0,
                course_id:  Number(f.course_id)   || 0,
                session_id: Number(f.session_id)  || 0,
                details:    f.details  || f.description || '',
                _offline:   true,
                _local_id:  localId,
            }),
        },
        {
            pattern:    /courses\/assignments\/create/,
            module:     'assignment',
            cacheStore: 'assignments_cache',
            label:      (f) => `Assignment: ${f.title || 'New Assignment'}`,
            redirect:   '/assignments',
            buildCache: (f, localId) => ({
                id:          'offline-' + localId,
                title:       f.title      || '',
                class_id:    Number(f.class_id)    || 0,
                section_id:  Number(f.section_id)  || 0,
                course_id:   Number(f.course_id)   || 0,
                session_id:  Number(f.session_id)  || 0,
                due_date:    f.due_date   || null,
                description: f.description || '',
                _offline:    true,
                _local_id:   localId,
            }),
        },
        {
            pattern:    /\/marks\/store/,
            module:     'marks',
            cacheStore: 'marks_cache',
            label:      (f) => `Marks entry — class ${f.class_id || '?'} / course ${f.course_id || '?'}`,
            redirect:   null,
            buildCache: (f, localId) => ({
                id:         'offline-' + localId,
                student_id: Number(f.student_id) || 0,
                course_id:  Number(f.course_id)  || 0,
                exam_id:    Number(f.exam_id)    || 0,
                session_id: Number(f.session_id) || 0,
                marks:      Number(f.marks)      || 0,
                _offline:   true,
                _local_id:  localId,
            }),
        },
        {
            pattern:    /\/notice\/create/,
            module:     'notice',
            cacheStore: 'notices_cache',
            label:      (f) => `Notice: ${f.notice_title || f.title || 'New Notice'}`,
            redirect:   null,
            buildCache: (f, localId) => ({
                id:          'offline-' + localId,
                notice_title:f.notice_title || f.title || '',
                description: f.description || f.notice_description || '',
                notice_date: f.notice_date || new Date().toISOString().slice(0, 10),
                class_id:    Number(f.class_id) || 0,
                _offline:    true,
                _local_id:   localId,
            }),
        },
        {
            pattern:    /\/attendances\/store/,
            module:     'attendance',
            cacheStore: null,   // attendance-sync.js handles its own queue
            label:      (f) => `Attendance — class ${f.class_id || '?'}`,
            redirect:   null,
            buildCache: null,
        },
        {
            pattern:    /\/admissions$/,
            module:     'admission',
            cacheStore: null,
            label:      (f) => `Admission: ${f.first_name || ''} ${f.last_name || ''}`.trim(),
            redirect:   '/admissions',
            buildCache: null,
        },
        {
            pattern:    /\/finance\/payments/,
            module:     'fee_collection',
            cacheStore: null,
            label:      (f) => `Fee Payment — invoice ${f.invoice_id || '?'}`,
            redirect:   null,
            buildCache: null,
        },
        {
            pattern:    /\/finance\/expenses/,
            module:     'expense',
            cacheStore: null,
            label:      (f) => `Expense: ${f.title || f.description || 'New Expense'}`,
            redirect:   '/finance/expenses',
            buildCache: null,
        },
        {
            pattern:    /\/finance\/income/,
            module:     'income',
            cacheStore: null,
            label:      (f) => `Income: ${f.title || f.description || 'New Income'}`,
            redirect:   '/finance/income',
            buildCache: null,
        },
    ];

    // ── Helpers ───────────────────────────────────────────────────────────────

    function log(msg, ...a) { console.log('[OfflineForms]', msg, ...a); }

    function getCsrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    }

    /** Collect all named form fields as a plain object, including checkboxes & multi-selects. */
    function serializeForm(form) {
        const data = {};
        const fd   = new FormData(form);
        for (const [key, value] of fd.entries()) {
            if (key === '_token') continue;
            if (key in data) {
                // Multiple values for the same key → array
                data[key] = [].concat(data[key], value);
            } else {
                data[key] = value;
            }
        }
        return data;
    }

    /** Resolve a form action URL to an absolute path for matching. */
    function actionPath(form) {
        try {
            return new URL(form.action, window.location.origin).pathname;
        } catch (_) {
            return form.action || '';
        }
    }

    /** Show a non-blocking toast notification. Falls back to alert if showToast() is absent. */
    function toast(message, type = 'info') {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type, 6000);
        } else {
            console.info('[OfflineForms toast]', type, message);
        }
    }

    /** Show an inline alert above the form telling the user it was saved offline. */
    function injectOfflineAlert(form, label) {
        // Remove any previous offline alert on this form
        form.parentElement?.querySelectorAll('.offline-form-alert').forEach(el => el.remove());

        const div = document.createElement('div');
        div.className = 'offline-form-alert';
        div.style.cssText = [
            'display:flex', 'align-items:flex-start', 'gap:0.75rem',
            'padding:0.875rem 1rem', 'margin-bottom:1rem',
            'background:#fef3c7', 'border:1px solid #fde68a',
            'border-radius:0.75rem', 'font-size:0.8125rem',
            'color:#92400e', 'line-height:1.5',
        ].join(';');

        div.innerHTML = `
            <i class="bi bi-wifi-off" style="font-size:1.1rem;flex-shrink:0;margin-top:0.05rem"></i>
            <div>
                <strong>Saved offline</strong> — <em>${label}</em> has been queued and will sync
                automatically when your internet connection is restored.<br>
                <span style="font-size:0.75rem;opacity:0.8">
                    To sync manually, click the <strong>Sync Now</strong> button in the banner.
                </span>
            </div>`;

        form.insertAdjacentElement('beforebegin', div);
    }

    // ── Core intercept handler ────────────────────────────────────────────────

    async function handleOfflineSubmit(e, form, mapEntry) {
        e.preventDefault();
        e.stopImmediatePropagation();

        const fields   = serializeForm(form);
        const labelStr = mapEntry.label(fields);

        log(`Intercepting offline submit: module=${mapEntry.module} label="${labelStr}"`);

        // 1. Queue in pending_sync
        let localId;
        try {
            localId = await offlineQueueAdd({
                module:   mapEntry.module,
                action:   'store',
                endpoint: actionPath(form),
                method:   (form.getAttribute('method') || 'POST').toUpperCase(),
                payload:  fields,
                label:    labelStr,
                meta:     { form_id: form.id || null, page: window.location.pathname },
            });
            log(`Queued local_id=${localId}`);
        } catch (err) {
            console.error('[OfflineForms] Queue write failed:', err);
            toast('Could not save offline — IndexedDB write failed.', 'error');
            return;
        }

        // 2. Write optimistic record to the matching cache store
        if (mapEntry.cacheStore && typeof mapEntry.buildCache === 'function') {
            try {
                const cacheRecord = mapEntry.buildCache(fields, localId);
                await offlineDbPut(mapEntry.cacheStore, cacheRecord);
                log(`Optimistic record written to ${mapEntry.cacheStore}`);
            } catch (err) {
                console.warn('[OfflineForms] Cache write failed (non-fatal):', err);
            }
        }

        // 3. Update the offline banner pending count
        if (typeof window.offlineSyncUpdateBadge === 'function') {
            window.offlineSyncUpdateBadge();
        }

        // 4. Show feedback
        injectOfflineAlert(form, labelStr);
        toast(`<i class="bi bi-check-circle-fill me-1"></i><strong>Saved offline</strong> — ${labelStr}. Will sync when connected.`, 'warning');

        // 5. Optionally redirect to list page
        if (mapEntry.redirect) {
            setTimeout(() => { window.location.href = mapEntry.redirect; }, 1800);
        }
    }

    // ── Attach intercept listeners on all matching forms ─────────────────────

    function attachToForm(form) {
        if (form.dataset.offlineIntercepted) return;
        form.dataset.offlineIntercepted = '1';

        const path  = actionPath(form);
        const entry = FORM_MAP.find((m) => m.pattern.test(path));
        if (!entry) return;

        form.addEventListener('submit', async function (e) {
            // Only intercept when genuinely offline
            if (navigator.onLine) return; // let it submit normally

            await handleOfflineSubmit(e, form, entry);
        }, { capture: true });

        log(`Attached offline intercept: ${path} → module:${entry.module}`);
    }

    function attachToAllForms() {
        document.querySelectorAll('form[method="post"], form[method="POST"]').forEach(attachToForm);
    }

    // ── Watch for dynamically injected forms (AJAX-rendered) ─────────────────

    function watchForNewForms() {
        if (!('MutationObserver' in window)) return;
        const obs = new MutationObserver((mutations) => {
            mutations.forEach((mut) => {
                mut.addedNodes.forEach((node) => {
                    if (node.nodeType !== 1) return;
                    if (node.tagName === 'FORM') {
                        attachToForm(node);
                    } else {
                        node.querySelectorAll?.('form[method="post"], form[method="POST"]')
                            .forEach(attachToForm);
                    }
                });
            });
        });
        obs.observe(document.body, { childList: true, subtree: true });
    }

    // ── Expose public helper so other scripts can also queue items manually ──

    /**
     * window.offlineQueueManual(module, endpoint, payload, label)
     * Queues any payload manually — useful for AJAX-based pages
     * that don't use a normal <form> element.
     */
    window.offlineQueueManual = async function (module, endpoint, payload, label) {
        const localId = await offlineQueueAdd({ module, action: 'store', endpoint, method: 'POST', payload, label });
        if (typeof window.offlineSyncUpdateBadge === 'function') window.offlineSyncUpdateBadge();
        toast(`<strong>Saved offline</strong> — ${label || module}. Will sync when connected.`, 'warning');
        return localId;
    };

    // ── Visual offline badge on cached list items ─────────────────────────────

    /**
     * Call this from list-page JS to render the "Pending sync" badge
     * on rows that came from IndexedDB (record._offline === true).
     */
    window.offlineMarkPendingRows = function (containerSelector, rowSelector) {
        const container = document.querySelector(containerSelector);
        if (!container) return;
        container.querySelectorAll(rowSelector).forEach((row) => {
            if (row.dataset.offline === '1' && !row.querySelector('.offline-row-badge')) {
                const badge = document.createElement('span');
                badge.className = 'offline-row-badge';
                badge.style.cssText = 'display:inline-flex;align-items:center;gap:0.25rem;font-size:0.65rem;font-weight:700;background:#fef3c7;color:#92400e;border:1px solid #fde68a;border-radius:9999px;padding:0.1rem 0.5rem';
                badge.innerHTML = '<i class="bi bi-clock-history"></i> Pending sync';
                row.appendChild(badge);
            }
        });
    };

    // ── Init ──────────────────────────────────────────────────────────────────

    function init() {
        attachToAllForms();
        watchForNewForms();
        log('Initialised — monitoring', document.querySelectorAll('form').length, 'form(s)');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }

}());
