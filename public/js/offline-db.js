/**
 * offline-db.js — IndexedDB wrapper for NepalSchoolHub offline-first layer (v3)
 *
 * Object stores:
 *   ── Queues ─────────────────────────────────────────────────────────
 *   pending_attendance   — attendance batch queue (legacy, kept for compat)
 *   pending_sync         — GENERIC offline queue for ALL modules
 *
 *   ── Reference-data caches (seeded on login) ─────────────────────────
 *   students_cache       — student roster
 *   classes_cache        — classes + sections
 *   staff_cache          — teachers / staff
 *   fee_structures_cache — fee structures per class
 *   fee_types_cache      — fee category master list
 *   subjects_cache       — subjects + course sections
 *   hostels_cache        — hostel rooms + beds
 *   transport_cache      — transport routes + stops
 *   reference_cache      — misc KV (academic sessions, school_id …)
 *
 *   ── Live data caches (seeded on login, written offline too) ──────────
 *   syllabi_cache        — existing syllabi (for listing while offline)
 *   assignments_cache    — existing assignments (for listing while offline)
 *   marks_cache          — marks entries per student/course
 *   notices_cache        — school notices
 *
 *   ── Logs ────────────────────────────────────────────────────────────
 *   sync_log             — audit trail of sync attempts
 *
 * All functions return Promises.
 * Load order: offline-db.js → offline-seed.js → attendance-sync.js → offline-forms.js → offline-sync.js
 */

const OFFLINE_DB_NAME    = 'schoolhub-offline';
const OFFLINE_DB_VERSION = 5;   // v5: adds complete finance (expenses, income, payments, day-book caches)

let _db = null;

/**
 * Open (or upgrade) the IndexedDB connection.
 * @returns {Promise<IDBDatabase>}
 */
function offlineDbOpen() {
    if (_db) return Promise.resolve(_db);

    return new Promise((resolve, reject) => {
        const req = indexedDB.open(OFFLINE_DB_NAME, OFFLINE_DB_VERSION);

        req.onupgradeneeded = (e) => {
            const db     = e.target.result;
            const oldVer = e.oldVersion || 0;

            // ── V1 BASELINE ──────────────────────────────────────────────
            if (oldVer < 1) {
                if (!db.objectStoreNames.contains('pending_attendance')) {
                    const pa = db.createObjectStore('pending_attendance', { keyPath: 'local_id', autoIncrement: true });
                    pa.createIndex('by_synced',    'synced',    { unique: false });
                    pa.createIndex('by_school_id', 'school_id', { unique: false });
                }
                if (!db.objectStoreNames.contains('students_cache')) {
                    const sc = db.createObjectStore('students_cache', { keyPath: 'student_id' });
                    sc.createIndex('by_class',   'class_id',   { unique: false });
                    sc.createIndex('by_section', 'section_id', { unique: false });
                }
                if (!db.objectStoreNames.contains('classes_cache')) {
                    db.createObjectStore('classes_cache', { keyPath: 'id' });
                }
                if (!db.objectStoreNames.contains('sync_log')) {
                    const sl = db.createObjectStore('sync_log', { keyPath: 'id', autoIncrement: true });
                    sl.createIndex('by_synced_at', 'synced_at', { unique: false });
                }
            }

            // ── V2 EXTENSION ─────────────────────────────────────────────
            if (oldVer < 2) {
                if (!db.objectStoreNames.contains('pending_sync')) {
                    const ps = db.createObjectStore('pending_sync', { keyPath: 'local_id', autoIncrement: true });
                    ps.createIndex('by_status',       'status',                   { unique: false });
                    ps.createIndex('by_module',       'module',                   { unique: false });
                    ps.createIndex('by_school_id',    'school_id',                { unique: false });
                    ps.createIndex('by_user_id',      'user_id',                  { unique: false });
                    ps.createIndex('by_created',      'created_at',               { unique: false });
                    ps.createIndex('by_school_status',['school_id','status'],      { unique: false });
                }
                if (!db.objectStoreNames.contains('staff_cache')) {
                    const s = db.createObjectStore('staff_cache', { keyPath: 'staff_id' });
                    s.createIndex('by_role',       'role_name',     { unique: false });
                    s.createIndex('by_department', 'department_id', { unique: false });
                    s.createIndex('by_active',     'is_active',     { unique: false });
                }
                if (!db.objectStoreNames.contains('fee_structures_cache')) {
                    const fs = db.createObjectStore('fee_structures_cache', { keyPath: 'id' });
                    fs.createIndex('by_class',   'class_id',   { unique: false });
                    fs.createIndex('by_session', 'session_id', { unique: false });
                }
                if (!db.objectStoreNames.contains('fee_types_cache')) {
                    db.createObjectStore('fee_types_cache', { keyPath: 'id' });
                }
                if (!db.objectStoreNames.contains('subjects_cache')) {
                    const sb = db.createObjectStore('subjects_cache', { keyPath: 'id' });
                    sb.createIndex('by_class',   'class_id',   { unique: false });
                    sb.createIndex('by_teacher', 'teacher_id', { unique: false });
                }
                if (!db.objectStoreNames.contains('hostels_cache')) {
                    const h = db.createObjectStore('hostels_cache', { keyPath: 'id' });
                    h.createIndex('by_type', 'type', { unique: false });
                }
                if (!db.objectStoreNames.contains('transport_cache')) {
                    db.createObjectStore('transport_cache', { keyPath: 'id' });
                }
                if (!db.objectStoreNames.contains('reference_cache')) {
                    db.createObjectStore('reference_cache', { keyPath: 'key' });
                }
                // Upgrade sync_log indexes if store already existed from v1
                try {
                    const slTx = e.target.transaction.objectStore('sync_log');
                    if (![...slTx.indexNames].includes('by_module')) {
                        slTx.createIndex('by_module',  'module',  { unique: false });
                        slTx.createIndex('by_outcome', 'outcome', { unique: false });
                    }
                } catch (_) { /* ignore if store doesn't exist yet */ }
            }

            // ── V3 EXTENSION — live data caches ──────────────────────────
            if (oldVer < 3) {
                // Syllabi (list view + offline creation)
                if (!db.objectStoreNames.contains('syllabi_cache')) {
                    const sy = db.createObjectStore('syllabi_cache', { keyPath: 'id' });
                    sy.createIndex('by_class',   'class_id',   { unique: false });
                    sy.createIndex('by_course',  'course_id',  { unique: false });
                    sy.createIndex('by_session', 'session_id', { unique: false });
                }

                // Assignments
                if (!db.objectStoreNames.contains('assignments_cache')) {
                    const as = db.createObjectStore('assignments_cache', { keyPath: 'id' });
                    as.createIndex('by_class',   'class_id',   { unique: false });
                    as.createIndex('by_course',  'course_id',  { unique: false });
                    as.createIndex('by_teacher', 'teacher_id', { unique: false });
                    as.createIndex('by_session', 'session_id', { unique: false });
                }

                // Marks (per student + course)
                if (!db.objectStoreNames.contains('marks_cache')) {
                    const mk = db.createObjectStore('marks_cache', { keyPath: 'id' });
                    mk.createIndex('by_student', 'student_id', { unique: false });
                    mk.createIndex('by_course',  'course_id',  { unique: false });
                    mk.createIndex('by_exam',    'exam_id',    { unique: false });
                    mk.createIndex('by_session', 'session_id', { unique: false });
                }

                // Notices
                if (!db.objectStoreNames.contains('notices_cache')) {
                    const nc = db.createObjectStore('notices_cache', { keyPath: 'id' });
                    nc.createIndex('by_date',  'notice_date', { unique: false });
                    nc.createIndex('by_class', 'class_id',    { unique: false });
                }

                // Exams (for marks form dropdowns)
                if (!db.objectStoreNames.contains('exams_cache')) {
                    const ex = db.createObjectStore('exams_cache', { keyPath: 'id' });
                    ex.createIndex('by_session', 'session_id', { unique: false });
                    ex.createIndex('by_class',   'class_id',   { unique: false });
                }
            }

            // ── V4 EXTENSION — comprehensive offline modules ─────────────
            if (oldVer < 4) {
                // Homework
                if (!db.objectStoreNames.contains('homework_cache')) {
                    const hw = db.createObjectStore('homework_cache', { keyPath: 'id' });
                    hw.createIndex('by_class',   'class_id',   { unique: false });
                    hw.createIndex('by_due',     'due_date',   { unique: false });
                    hw.createIndex('by_session', 'session_id', { unique: false });
                }

                // Routines / Timetables
                if (!db.objectStoreNames.contains('routines_cache')) {
                    const rt = db.createObjectStore('routines_cache', { keyPath: 'id' });
                    rt.createIndex('by_class',   'class_id',   { unique: false });
                    rt.createIndex('by_day',     'weekday',    { unique: false });
                    rt.createIndex('by_teacher', 'teacher_id', { unique: false });
                }

                // Fee Invoices
                if (!db.objectStoreNames.contains('invoices_cache')) {
                    const inv = db.createObjectStore('invoices_cache', { keyPath: 'id' });
                    inv.createIndex('by_student', 'student_id',     { unique: false });
                    inv.createIndex('by_status',  'status',         { unique: false });
                    inv.createIndex('by_number',  'invoice_number', { unique: false });
                }

                // Visitors
                if (!db.objectStoreNames.contains('visitors_cache')) {
                    const vs = db.createObjectStore('visitors_cache', { keyPath: 'id' });
                    vs.createIndex('by_status', 'status', { unique: false });
                }

                // Leave Applications
                if (!db.objectStoreNames.contains('leave_cache')) {
                    const lv = db.createObjectStore('leave_cache', { keyPath: 'id' });
                    lv.createIndex('by_user',   'user_id', { unique: false });
                    lv.createIndex('by_status', 'status',  { unique: false });
                }

                // Feedback
                if (!db.objectStoreNames.contains('feedback_cache')) {
                    const fb = db.createObjectStore('feedback_cache', { keyPath: 'id' });
                    fb.createIndex('by_status', 'status', { unique: false });
                }
            }

            // ── V5 EXTENSION — Complete Offline Finance & POS Architecture ────
            if (oldVer < 5) {
                // Expenses
                if (!db.objectStoreNames.contains('expenses_cache')) {
                    const exp = db.createObjectStore('expenses_cache', { keyPath: 'id' });
                    exp.createIndex('by_category', 'category',     { unique: false });
                    exp.createIndex('by_date',     'expense_date', { unique: false });
                }

                // Income
                if (!db.objectStoreNames.contains('income_cache')) {
                    const inc = db.createObjectStore('income_cache', { keyPath: 'id' });
                    inc.createIndex('by_category', 'category',    { unique: false });
                    inc.createIndex('by_date',     'income_date', { unique: false });
                }

                // Payments & Receipts
                if (!db.objectStoreNames.contains('payments_cache')) {
                    const pay = db.createObjectStore('payments_cache', { keyPath: 'id' });
                    pay.createIndex('by_invoice', 'invoice_id',   { unique: false });
                    pay.createIndex('by_date',    'payment_date', { unique: false });
                }

                // Day Book Summary
                if (!db.objectStoreNames.contains('day_book_cache')) {
                    db.createObjectStore('day_book_cache', { keyPath: 'date' });
                }
            }
        };

        req.onsuccess = (e) => {
            _db = e.target.result;
            _db.onversionchange = () => { if (_db) { _db.close(); _db = null; } };
            resolve(_db);
        };
        req.onerror   = (e) => { console.error('[OfflineDB] Open failed:', e.target.error); reject(e.target.error); };
        req.onblocked = ()  => { console.warn('[OfflineDB] Upgrade blocked — close other tabs first.'); };
    });
}

// ═════════════════════════════════════════════════════════════════════════
// Generic CRUD helpers
// ═════════════════════════════════════════════════════════════════════════

function offlineDbAdd(storeName, record) {
    return offlineDbOpen().then((db) => new Promise((resolve, reject) => {
        const tx    = db.transaction(storeName, 'readwrite');
        const store = tx.objectStore(storeName);
        const req   = store.add(record);
        req.onsuccess = () => resolve(req.result);
        req.onerror   = () => reject(req.error);
    }));
}

function offlineDbPut(storeName, record) {
    return offlineDbOpen().then((db) => new Promise((resolve, reject) => {
        const tx    = db.transaction(storeName, 'readwrite');
        const store = tx.objectStore(storeName);
        const req   = store.put(record);
        req.onsuccess = () => resolve(req.result);
        req.onerror   = () => reject(req.error);
    }));
}

function offlineDbGet(storeName, key) {
    return offlineDbOpen().then((db) => new Promise((resolve, reject) => {
        const tx    = db.transaction(storeName, 'readonly');
        const store = tx.objectStore(storeName);
        const req   = store.get(key);
        req.onsuccess = () => resolve(req.result);
        req.onerror   = () => reject(req.error);
    }));
}

function offlineDbGetAll(storeName) {
    return offlineDbOpen().then((db) => new Promise((resolve, reject) => {
        const tx    = db.transaction(storeName, 'readonly');
        const store = tx.objectStore(storeName);
        const req   = store.getAll();
        req.onsuccess = () => resolve(req.result);
        req.onerror   = () => reject(req.error);
    }));
}

function offlineDbGetByIndex(storeName, indexName, value) {
    return offlineDbOpen().then((db) => new Promise((resolve, reject) => {
        const tx    = db.transaction(storeName, 'readonly');
        const store = tx.objectStore(storeName);
        const index = store.index(indexName);
        const req   = index.getAll(value);
        req.onsuccess = () => resolve(req.result);
        req.onerror   = () => reject(req.error);
    }));
}

function offlineDbDelete(storeName, key) {
    return offlineDbOpen().then((db) => new Promise((resolve, reject) => {
        const tx    = db.transaction(storeName, 'readwrite');
        const store = tx.objectStore(storeName);
        const req   = store.delete(key);
        req.onsuccess = () => resolve();
        req.onerror   = () => reject(req.error);
    }));
}

function offlineDbUpdate(storeName, key, updates) {
    return offlineDbOpen().then((db) => new Promise((resolve, reject) => {
        const tx     = db.transaction(storeName, 'readwrite');
        const store  = tx.objectStore(storeName);
        const getReq = store.get(key);
        getReq.onsuccess = () => {
            const updated = Object.assign({}, getReq.result, updates);
            const putReq  = store.put(updated);
            putReq.onsuccess = () => resolve();
            putReq.onerror   = () => reject(putReq.error);
        };
        getReq.onerror = () => reject(getReq.error);
    }));
}

function offlineDbClear(storeName) {
    return offlineDbOpen().then((db) => new Promise((resolve, reject) => {
        const tx    = db.transaction(storeName, 'readwrite');
        const store = tx.objectStore(storeName);
        const req   = store.clear();
        req.onsuccess = () => resolve();
        req.onerror   = () => reject(req.error);
    }));
}

function offlineDbBulkPut(storeName, records) {
    if (!records || !records.length) return Promise.resolve();
    return offlineDbOpen().then((db) => new Promise((resolve, reject) => {
        const tx    = db.transaction(storeName, 'readwrite');
        const store = tx.objectStore(storeName);
        records.forEach((r) => store.put(r));
        tx.oncomplete = () => resolve();
        tx.onerror    = () => reject(tx.error);
    }));
}

function offlineDbCount(storeName, indexName, value) {
    return offlineDbOpen().then((db) => new Promise((resolve, reject) => {
        const tx     = db.transaction(storeName, 'readonly');
        const store  = tx.objectStore(storeName);
        const target = indexName ? store.index(indexName) : store;
        const req    = value !== undefined ? target.count(value) : target.count();
        req.onsuccess = () => resolve(req.result);
        req.onerror   = () => reject(req.error);
    }));
}

// ═════════════════════════════════════════════════════════════════════════
// Generic Pending Sync Queue helpers
// ═════════════════════════════════════════════════════════════════════════

/** All modules that can be queued offline. */
window.OFFLINE_MODULES = {
    student:           { label: 'Add Student',                icon: 'bi-person-plus',    priority: 1 },
    syllabus:          { label: 'Create Syllabus',            icon: 'bi-journal-text',   priority: 2 },
    assignment:        { label: 'Create Assignment',          icon: 'bi-file-earmark',   priority: 2 },
    homework:          { label: 'Daily Homework',             icon: 'bi-pencil-square',  priority: 2 },
    homework_sign:     { label: 'Parent Diary Sign-Off',      icon: 'bi-pen-fill',       priority: 2 },
    marks:             { label: 'Submit Marks',               icon: 'bi-bar-chart',      priority: 2 },
    notice:            { label: 'Post Notice',                icon: 'bi-megaphone',      priority: 3 },
    attendance:        { label: 'Student Attendance',         icon: 'bi-calendar2-check',priority: 1 },
    admission:         { label: 'Student Admission',          icon: 'bi-person-plus',    priority: 1 },
    fee_collection:    { label: 'Fee Collection',             icon: 'bi-cash-coin',      priority: 2 },
    expense:           { label: 'Expense Voucher',            icon: 'bi-receipt-cut',    priority: 3 },
    income:            { label: 'Income Receipt',             icon: 'bi-wallet2',        priority: 3 },
    visitor:           { label: 'Visitor Check-in',           icon: 'bi-person-badge-fill', priority: 3 },
    leave:             { label: 'Leave Request',              icon: 'bi-calendar-x',     priority: 3 },
    feedback:          { label: 'Feedback / Grievance',       icon: 'bi-chat-heart',     priority: 4 },
    gradebook:         { label: 'Marks / Grade Entry',        icon: 'bi-journal-text',   priority: 2 },
    staff:             { label: 'Staff / HR Record',          icon: 'bi-person-badge',   priority: 3 },
    library_issue:     { label: 'Library Book Issue/Return',  icon: 'bi-book',           priority: 3 },
    hostel_allocation: { label: 'Hostel Allocation',          icon: 'bi-house-door',     priority: 3 },
    transport:         { label: 'Transport Allocation',       icon: 'bi-bus-front',      priority: 3 },
    custom:            { label: 'Custom Form Submission',     icon: 'bi-ui-checks-grid', priority: 5 },
};

/**
 * Add a record to the generic pending-sync queue.
 *
 * @param {object} entry
 * @param {string} entry.module    — key from OFFLINE_MODULES
 * @param {string} entry.action    — 'store' | 'update' | 'destroy'
 * @param {string} entry.endpoint  — server route to replay to (e.g. '/student/create')
 * @param {string} [entry.method]  — HTTP method override, default: 'POST'
 * @param {object} entry.payload   — form fields as plain object
 * @param {string} [entry.label]   — human-readable summary for the sync panel
 * @param {object} [entry.meta]    — extra context (optimistic local_id, etc.)
 * @returns {Promise<number>}       assigned local_id
 */
function offlineQueueAdd(entry) {
    const nowIso    = new Date().toISOString();
    const moduleDef = window.OFFLINE_MODULES[entry.module] || window.OFFLINE_MODULES.custom;
    const record    = Object.assign({
        status:          'pending',
        retry_count:     0,
        last_error:      null,
        last_attempt:    null,
        synced_at:       null,
        server_response: null,
        created_at:      nowIso,
        updated_at:      nowIso,
        method:          'POST',
        label:           entry.label || moduleDef.label,
        priority:        moduleDef.priority,
        meta:            {},
    }, entry);

    return offlineDbAdd('pending_sync', record);
}

/** Get all pending queue items sorted by priority desc, newest first. */
function offlineQueueGetAll() {
    return offlineDbGetAll('pending_sync')
        .then((arr) => arr.sort((a, b) =>
            (a.priority - b.priority) || (new Date(a.created_at) - new Date(b.created_at))
        ));
}

/** Get counts grouped by status. */
function offlineQueueStatusCounts() {
    return offlineDbGetAll('pending_sync').then((arr) => {
        const counts = { pending: 0, syncing: 0, failed: 0, synced: 0, total: arr.length };
        arr.forEach((r) => { counts[r.status] = (counts[r.status] || 0) + 1; });
        return counts;
    });
}

/** Count only non-synced (pending + failed) items. */
function offlineQueuePendingCount() {
    return offlineDbGetAll('pending_sync').then((arr) =>
        arr.filter((r) => r.status === 'pending' || r.status === 'failed').length
    );
}

/** Write to sync_log. */
function offlineSyncLog(entry) {
    return offlineDbAdd('sync_log', Object.assign({ synced_at: new Date().toISOString() }, entry));
}

/** Reference KV helpers. */
function offlineRefGet(key)        { return offlineDbGet('reference_cache', key).then((r) => r ? r.value : null); }
function offlineRefPut(key, value) { return offlineDbPut('reference_cache', { key, value, updated_at: new Date().toISOString() }); }

/**
 * Expose DB version for diagnostics.
 * Usage: window.offlineDbVersion()
 */
window.offlineDbVersion = () => OFFLINE_DB_VERSION;
