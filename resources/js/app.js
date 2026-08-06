/**
 * app.js — School ERP Modern UI  (Module 20)
 * ─────────────────────────────────────────────
 * Bundled with Laravel Mix / webpack.
 * Covers:
 *   1. Dark mode  (persist in localStorage)
 *   2. Sidebar (mobile open/close, desktop collapse)
 *   3. Notification panel toggle
 *   4. User menu dropdown toggle
 *   5. Toast notification system
 *   6. Command palette  (Cmd/Ctrl + K)
 *   7. Floating Action Button (FAB)
 *   8. Keyboard shortcuts
 *   9. Auto-show session toasts from DOM markers
 *  10. Table column sorting (client-side)
 *  11. Inline search/filter for tables
 *  12. Skeleton loader helpers
 *  13. Confirm-delete helper
 *  14. Tooltip init (title → custom tooltip)
 */

'use strict';

/* ============================================================
   1. DARK MODE
   ============================================================ */
const THEME_KEY = 'erp_theme';

function getTheme() {
    return localStorage.getItem(THEME_KEY) || 'light';
}

function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    if (theme === 'dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
    localStorage.setItem(THEME_KEY, theme);
}

function toggleTheme() {
    applyTheme(getTheme() === 'dark' ? 'light' : 'dark');
}

// Apply on load (also done inline in <head> to avoid flash)
applyTheme(getTheme());

/* ============================================================
   2. SIDEBAR  (mobile + desktop collapse)
   ============================================================ */
const SIDEBAR_COLLAPSED_KEY = 'erp_sidebar_collapsed';

function openSidebar() {
    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sidebar-overlay');
    if (!sidebar) return;
    sidebar.classList.remove('-translate-x-full');
    if (overlay) overlay.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeSidebar() {
    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sidebar-overlay');
    if (!sidebar) return;
    sidebar.classList.add('-translate-x-full');
    if (overlay) overlay.classList.add('hidden');
    document.body.style.overflow = '';
}

function toggleSidebarCollapse() {
    const shell     = document.getElementById('app-shell');
    const sidebar   = document.getElementById('sidebar');
    const colBtn    = document.getElementById('sidebar-collapse-btn');
    if (!shell || !sidebar) return;

    const isCollapsed = shell.classList.toggle('sidebar-collapsed');
    localStorage.setItem(SIDEBAR_COLLAPSED_KEY, isCollapsed ? '1' : '0');

    if (colBtn) {
        colBtn.setAttribute('title', isCollapsed ? 'Expand sidebar (⌘B)' : 'Collapse sidebar (⌘B)');
        colBtn.querySelector('i').className = isCollapsed ? 'bi bi-layout-sidebar-reverse text-base' : 'bi bi-layout-sidebar text-base';
    }
}

function restoreSidebarState() {
    if (localStorage.getItem(SIDEBAR_COLLAPSED_KEY) === '1') {
        const shell = document.getElementById('app-shell');
        if (shell) shell.classList.add('sidebar-collapsed');
    }
}

/* ============================================================
   3. NOTIFICATION PANEL DROPDOWN
   ============================================================ */
function toggleNotifPanel() {
    const panel = document.getElementById('notif-panel');
    if (!panel) return;
    const isHidden = panel.classList.toggle('hidden');
    if (!isHidden) {
        // close user menu if open
        const um = document.getElementById('user-menu-panel');
        if (um) um.classList.add('hidden');
    }
}

/* ============================================================
   4. USER MENU DROPDOWN
   ============================================================ */
function toggleUserMenu() {
    const panel = document.getElementById('user-menu-panel');
    if (!panel) return;
    const isHidden = panel.classList.toggle('hidden');
    if (!isHidden) {
        // close notif panel if open
        const np = document.getElementById('notif-panel');
        if (np) np.classList.add('hidden');
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function (e) {
    // Notification panel
    const nw = document.getElementById('notif-wrapper');
    const np = document.getElementById('notif-panel');
    if (np && nw && !nw.contains(e.target)) {
        np.classList.add('hidden');
    }
    // User menu
    const uw = document.getElementById('user-menu-wrapper');
    const um = document.getElementById('user-menu-panel');
    if (um && uw && !uw.contains(e.target)) {
        um.classList.add('hidden');
    }
    // FAB
    const fab = document.getElementById('fab-group');
    if (fab && !fab.contains(e.target)) {
        closeFab();
    }
});

/* ============================================================
   5. TOAST SYSTEM
   ============================================================ */
const TOAST_ICONS = {
    success: '<i class="bi bi-check-lg"></i>',
    error:   '<i class="bi bi-x-lg"></i>',
    warning: '<i class="bi bi-exclamation-lg"></i>',
    info:    '<i class="bi bi-info-lg"></i>',
};

const TOAST_TITLES = {
    success: 'Success',
    error:   'Error',
    warning: 'Warning',
    info:    'Info',
};

/**
 * Show a toast notification.
 * @param {string} msg     - Message text
 * @param {string} type    - 'success' | 'error' | 'warning' | 'info'
 * @param {string} [title] - Optional override title
 * @param {number} [duration] - ms before auto-dismiss (0 = persistent)
 */
function showToast(msg, type = 'info', title = null, duration = 4000) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const t = document.createElement('div');
    t.className = `toast-item toast-${type}`;
    t.innerHTML = `
        <div class="toast-icon">${TOAST_ICONS[type] || TOAST_ICONS.info}</div>
        <div class="toast-body">
            <div class="toast-title">${title || TOAST_TITLES[type] || 'Notice'}</div>
            <div class="toast-msg">${msg}</div>
        </div>
        <button class="toast-close" onclick="dismissToast(this.closest('.toast-item'))"
                aria-label="Dismiss">
            <i class="bi bi-x"></i>
        </button>`;

    container.appendChild(t);

    // Trigger transition
    requestAnimationFrame(() => {
        requestAnimationFrame(() => t.classList.add('toast-show'));
    });

    if (duration > 0) {
        setTimeout(() => dismissToast(t), duration);
    }

    return t;
}

function dismissToast(el) {
    if (!el) return;
    el.classList.remove('toast-show');
    el.classList.add('toast-hide');
    setTimeout(() => el.remove(), 350);
}

// Auto-show toasts injected from Blade (session flashes)
function initAutoToasts() {
    document.querySelectorAll('.toast-auto-show').forEach(function (el) {
        const type = el.dataset.type || 'info';
        const msg  = el.dataset.msg  || '';
        if (msg) showToast(msg, type);
        el.remove();
    });
}

/* ============================================================
   6. COMMAND PALETTE  (Cmd/Ctrl + K)
   ============================================================ */

// Registry of navigable commands (static + dynamically extendable)
const CMD_REGISTRY = [
    // Navigation
    { label: 'Dashboard',          icon: 'bi-grid-1x2',               category: 'Navigation', href: '/home',                       shortcut: 'G H' },
    { label: 'Students — List',    icon: 'bi-people',                  category: 'Navigation', href: '/students/view' },
    { label: 'Students — Add',     icon: 'bi-person-plus',             category: 'Navigation', href: '/students/add' },
    { label: 'Teachers — List',    icon: 'bi-person-badge',            category: 'Navigation', href: '/teachers/view/list' },
    { label: 'Attendance — Take',  icon: 'bi-check2-square',           category: 'Navigation', href: '/attendances/take' },
    { label: 'Attendance — View',  icon: 'bi-calendar-week',           category: 'Navigation', href: '/attendances/view' },
    { label: 'Exams — List',       icon: 'bi-file-text',               category: 'Navigation', href: '/exams/view' },
    { label: 'Marks — Enter',      icon: 'bi-pencil-square',           category: 'Navigation', href: '/marks/create' },
    { label: 'Results',            icon: 'bi-award',                   category: 'Navigation', href: '/results' },
    { label: 'Timetable',          icon: 'bi-calendar4-range',         category: 'Navigation', href: '/routine' },
    { label: 'Library',            icon: 'bi-journals',                category: 'Navigation', href: '/library' },
    { label: 'Transport',          icon: 'bi-bus-front',               category: 'Navigation', href: '/transport' },
    { label: 'Hostel',             icon: 'bi-building',                category: 'Navigation', href: '/hostel/hostels' },
    { label: 'Finance',            icon: 'bi-cash-stack',              category: 'Navigation', href: '/finance' },
    { label: 'HR Dashboard',       icon: 'bi-people-fill',             category: 'Navigation', href: '/hr' },
    { label: 'Inventory',          icon: 'bi-boxes',                   category: 'Navigation', href: '/inventory' },
    { label: 'Analytics',          icon: 'bi-pie-chart',               category: 'Navigation', href: '/analytics/dashboard' },
    { label: 'Reports',            icon: 'bi-file-earmark-spreadsheet',category: 'Navigation', href: '/reports' },
    { label: 'Notices',            icon: 'bi-megaphone',               category: 'Navigation', href: '/notice/create' },
    { label: 'Notifications',      icon: 'bi-bell',                    category: 'Navigation', href: '/notifications' },
    { label: 'Roles & Permissions',icon: 'bi-shield-check',            category: 'Navigation', href: '/roles' },
    { label: 'Academic Settings',  icon: 'bi-tools',                   category: 'Navigation', href: '/academics/settings' },
    { label: 'AI Dashboard',       icon: 'bi-robot',                   category: 'Navigation', href: '/ai-features' },
    { label: 'File Manager',       icon: 'bi-folder2-open',            category: 'Navigation', href: '/files' },
    // Actions
    { label: 'Toggle dark mode',   icon: 'bi-moon-fill',               category: 'Actions',    action: 'toggleTheme' },
    { label: 'Change password',    icon: 'bi-key',                     category: 'Actions',    href: '/password/change' },
    { label: 'Security (2FA)',     icon: 'bi-shield-lock',             category: 'Actions',    href: '/two-factor/setup' },
    { label: 'Keyboard shortcuts', icon: 'bi-keyboard',               category: 'Actions',    action: 'showShortcuts' },
];

let cmdSelected = -1;

function openCommandPalette() {
    const backdrop = document.getElementById('cmd-palette-backdrop');
    const input    = document.getElementById('cmd-input');
    if (!backdrop) return;
    backdrop.classList.add('active');
    backdrop.style.display = 'flex';
    if (input) {
        input.value = '';
        input.focus();
    }
    renderCmdResults('');
    cmdSelected = -1;
}

function closeCommandPalette() {
    const backdrop = document.getElementById('cmd-palette-backdrop');
    if (backdrop) {
        backdrop.classList.remove('active');
        backdrop.style.display = 'none';
    }
    cmdSelected = -1;
}

function highlight(text, query) {
    if (!query) return text;
    const re = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
    return text.replace(re, '<mark>$1</mark>');
}

function renderCmdResults(query) {
    const container = document.getElementById('cmd-results');
    if (!container) return;

    const q = query.trim().toLowerCase();
    const matched = CMD_REGISTRY.filter(c =>
        c.label.toLowerCase().includes(q) || c.category.toLowerCase().includes(q)
    );

    console.log('renderCmdResults called', { query, q, matchedCount: matched.length });

    // If query is empty or too short, show static results only
    if (!q || q.length < 2) {
        if (!matched.length) {
            container.innerHTML = `<div class="px-4 py-6 text-center text-sm" style="color:var(--text-muted)">
                <i class="bi bi-search-heart d-block" style="font-size:1.5rem;margin-bottom:.5rem"></i>
                Type to search...
            </div>`;
            return;
        }
        renderStaticResults(matched, q, container);
        return;
    }

    console.log('Fetching search results for:', q);

    // For longer queries, fetch dynamic results from API
    fetch(`/search?q=${encodeURIComponent(q)}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => {
        console.log('Fetch response status:', r.status);
        if (!r.ok) {
            console.error('Search API error:', r.status, r.statusText);
            throw new Error('API error');
        }
        return r.json();
    })
    .then(response => {
        console.log('Search results:', response);
        let html = '';
        
        // Add dynamic results first
        if (response.data && response.data.length > 0) {
            html += `<div class="cmd-group-label">People</div>`;
            response.data.forEach((item, i) => {
                const label = highlight(item.label, q);
                const sublabel = highlight(item.sublabel, q);
                html += `<a class="cmd-result-item" href="${item.href}" data-cmd-idx="${i}">
                    <span class="cmd-icon"><i class="bi ${item.icon}"></i></span>
                    <div class="cmd-result-content">
                        <span class="cmd-label">${label}</span>
                        <span class="cmd-sublabel">${sublabel}</span>
                    </div>
                </a>`;
            });
        }

        // Add static navigation results
        if (matched.length > 0) {
            html += `<div class="cmd-group-label">Navigation</div>`;
            matched.forEach((c, i) => {
                const label = highlight(c.label, q);
                const shortcut = c.shortcut ? `<span class="cmd-shortcut">${c.shortcut}</span>` : '';
                const tag = c.href ? 'a' : 'button';
                const hrefAttr = c.href ? `href="${c.href}"` : '';
                const actionAttr = c.action ? `onclick="cmdRunAction('${c.action}')"` : '';
                html += `<${tag} class="cmd-result-item" ${hrefAttr} ${actionAttr} data-cmd-idx="${i}">
                    <span class="cmd-icon"><i class="bi ${c.icon}"></i></span>
                    <span class="cmd-label">${label}</span>
                    ${shortcut}
                </${tag}>`;
            });
        }

        if (!html) {
            html = `<div class="px-4 py-6 text-center text-sm" style="color:var(--text-muted)">
                <i class="bi bi-search-heart d-block" style="font-size:1.5rem;margin-bottom:.5rem"></i>
                No results for "${query}"
            </div>`;
        }

        container.innerHTML = html;
        cmdSelected = -1;
    })
    .catch((error) => {
        console.error('Search fetch error:', error);
        // Fallback to static results on error
        renderStaticResults(matched, q, container);
    });
}

function renderStaticResults(matched, query, container) {
    if (!matched.length) {
        container.innerHTML = `<div class="px-4 py-6 text-center text-sm" style="color:var(--text-muted)">
            <i class="bi bi-search-heart d-block" style="font-size:1.5rem;margin-bottom:.5rem"></i>
            No results for "${query}"
        </div>`;
        return;
    }

    // Group by category
    const groups = {};
    matched.forEach(c => {
        if (!groups[c.category]) groups[c.category] = [];
        groups[c.category].push(c);
    });

    let html = '';
    Object.keys(groups).forEach(cat => {
        html += `<div class="cmd-group-label">${cat}</div>`;
        groups[cat].forEach((c, i) => {
            const label = highlight(c.label, query);
            const shortcut = c.shortcut ? `<span class="cmd-shortcut">${c.shortcut}</span>` : '';
            const tag = c.href ? 'a' : 'button';
            const hrefAttr = c.href ? `href="${c.href}"` : '';
            const actionAttr = c.action ? `onclick="cmdRunAction('${c.action}')"` : '';
            html += `<${tag} class="cmd-result-item" ${hrefAttr} ${actionAttr} data-cmd-idx="${i}">
                <span class="cmd-icon"><i class="bi ${c.icon}"></i></span>
                <span class="cmd-label">${label}</span>
                ${shortcut}
            </${tag}>`;
        });
    });

    container.innerHTML = html;
    cmdSelected = -1;
}

function filterCmdResults(val) {
    renderCmdResults(val);
}

function cmdRunAction(action) {
    closeCommandPalette();
    if (action === 'toggleTheme')  toggleTheme();
    if (action === 'showShortcuts') openShortcutModal();
}

function cmdKeyNav(e) {
    const items = document.querySelectorAll('#cmd-results .cmd-result-item');
    if (!items.length) return;

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        cmdSelected = Math.min(cmdSelected + 1, items.length - 1);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        cmdSelected = Math.max(cmdSelected - 1, 0);
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (cmdSelected >= 0 && items[cmdSelected]) items[cmdSelected].click();
        else if (items[0]) items[0].click();
        closeCommandPalette();
        return;
    } else if (e.key === 'Escape') {
        closeCommandPalette();
        return;
    }

    items.forEach((el, i) => el.classList.toggle('active', i === cmdSelected));
    if (items[cmdSelected]) items[cmdSelected].scrollIntoView({ block: 'nearest' });
}

/* ============================================================
   7. FLOATING ACTION BUTTON (FAB)
   ============================================================ */
let fabOpen = false;

function toggleFab() {
    fabOpen = !fabOpen;
    const actions = document.getElementById('fab-actions');
    const icon    = document.getElementById('fab-icon');
    if (!actions) return;

    if (fabOpen) {
        actions.classList.remove('opacity-0', 'translate-y-2', 'pointer-events-none');
        if (icon) { icon.className = 'bi bi-x-lg text-xl'; }
    } else {
        closeFab();
    }
}

function closeFab() {
    fabOpen = false;
    const actions = document.getElementById('fab-actions');
    const icon    = document.getElementById('fab-icon');
    if (actions) actions.classList.add('opacity-0', 'translate-y-2', 'pointer-events-none');
    if (icon)    icon.className = 'bi bi-plus-lg text-xl';
}

/* ============================================================
   8. KEYBOARD SHORTCUT MODAL
   ============================================================ */
function openShortcutModal() {
    const m = document.getElementById('shortcut-modal');
    if (m) { m.style.display = 'flex'; m.classList.remove('hidden'); }
}

function closeShortcutModal() {
    const m = document.getElementById('shortcut-modal');
    if (m) { m.style.display = 'none'; }
}

/* ============================================================
   8b. GLOBAL KEYBOARD SHORTCUTS
   ============================================================ */
let gKeySeq = '';
let gKeyTimer = null;

document.addEventListener('keydown', function (e) {
    const tag = document.activeElement?.tagName?.toLowerCase();
    const isInput = tag === 'input' || tag === 'textarea' || tag === 'select'
                 || document.activeElement?.isContentEditable;

    // Cmd/Ctrl + K → command palette
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        openCommandPalette();
        return;
    }

    // Cmd/Ctrl + B → toggle sidebar
    if ((e.metaKey || e.ctrlKey) && e.key === 'b') {
        e.preventDefault();
        toggleSidebarCollapse();
        return;
    }

    // Cmd/Ctrl + D → toggle dark mode
    if ((e.metaKey || e.ctrlKey) && e.key === 'd') {
        e.preventDefault();
        toggleTheme();
        return;
    }

    // ESC → close palette / modals
    if (e.key === 'Escape') {
        closeCommandPalette();
        closeShortcutModal();
        closeFab();
        return;
    }

    // ? → keyboard shortcuts help (when not typing)
    if (!isInput && e.key === '?') {
        openShortcutModal();
        return;
    }

    // Two-key sequences (G H → home, etc.) — when not in input
    if (!isInput) {
        gKeySeq += e.key.toUpperCase();
        clearTimeout(gKeyTimer);
        gKeyTimer = setTimeout(() => { gKeySeq = ''; }, 800);

        const sequences = {
            'GH': '/home',
            'GS': '/students/view',
            'GT': '/teachers/view/list',
            'GA': '/attendances/take',
            'GE': '/exams/view',
            'GL': '/library',
        };

        if (sequences[gKeySeq]) {
            window.location.href = sequences[gKeySeq];
            gKeySeq = '';
        }
    }
});

/* ============================================================
   9. CLIENT-SIDE TABLE SORT
   ============================================================ */
function initTableSort() {
    document.querySelectorAll('.table-modern th.sortable, table th.sortable').forEach(function (th) {
        th.addEventListener('click', function () {
            const table = th.closest('table');
            if (!table) return;

            const idx      = Array.from(th.parentElement.children).indexOf(th);
            const tbody    = table.querySelector('tbody');
            const rows     = Array.from(tbody?.querySelectorAll('tr') || []);
            const isAsc    = th.classList.contains('sort-asc');

            // Reset all headers
            table.querySelectorAll('th').forEach(h => {
                h.classList.remove('sort-asc', 'sort-desc');
                const si = h.querySelector('.sort-icon');
                if (si) si.textContent = '';
            });

            // Apply sort direction
            th.classList.add(isAsc ? 'sort-desc' : 'sort-asc');

            rows.sort(function (a, b) {
                const aText = a.cells[idx]?.textContent?.trim() || '';
                const bText = b.cells[idx]?.textContent?.trim() || '';
                const aNum  = parseFloat(aText.replace(/[^0-9.-]/g, ''));
                const bNum  = parseFloat(bText.replace(/[^0-9.-]/g, ''));
                const numericSort = !isNaN(aNum) && !isNaN(bNum);
                const cmp = numericSort ? aNum - bNum : aText.localeCompare(bText, undefined, { sensitivity: 'base' });
                return isAsc ? -cmp : cmp;
            });

            rows.forEach(r => tbody.appendChild(r));
        });
    });
}

/* ============================================================
   10. CLIENT-SIDE TABLE SEARCH / FILTER
   ============================================================ */
function initTableSearch() {
    document.querySelectorAll('[data-table-search]').forEach(function (input) {
        const targetId = input.dataset.tableSearch;
        const table    = document.getElementById(targetId);
        if (!table) return;

        input.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            table.querySelectorAll('tbody tr').forEach(function (row) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });

            // Show empty state
            const tbody       = table.querySelector('tbody');
            const visibleRows = tbody ? Array.from(tbody.querySelectorAll('tr')).filter(r => r.style.display !== 'none') : [];
            let empty = table.parentElement?.querySelector('.table-empty-state');
            if (visibleRows.length === 0 && !empty) {
                empty = document.createElement('div');
                empty.className = 'table-empty-state px-6 py-10 text-center text-sm';
                empty.style.color = 'var(--text-muted)';
                empty.innerHTML = '<i class="bi bi-search" style="font-size:1.5rem;display:block;margin-bottom:.5rem"></i>No results match your search.';
                table.parentElement.appendChild(empty);
            } else if (visibleRows.length > 0 && empty) {
                empty.remove();
            }
        });
    });
}

/* ============================================================
   11. CONFIRM-DELETE HELPER  (data-confirm attribute)
   ============================================================ */
function initConfirmDelete() {
    document.addEventListener('submit', function (e) {
        const form = e.target;
        const msg  = form.dataset.confirm;
        if (msg && !confirm(msg)) {
            e.preventDefault();
        }
    });

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-confirm]');
        if (btn && btn.tagName !== 'FORM') {
            if (!confirm(btn.dataset.confirm)) {
                e.preventDefault();
                e.stopPropagation();
            }
        }
    });
}

/* ============================================================
   12. TOOLTIP  (data-tooltip attribute → simple tooltip)
   ============================================================ */
function initTooltips() {
    let tip = null;

    document.addEventListener('mouseover', function (e) {
        const el = e.target.closest('[data-tooltip]');
        if (!el) return;
        const text = el.getAttribute('data-tooltip');
        if (!text) return;

        tip = document.createElement('div');
        tip.textContent = text;
        tip.style.cssText = `
            position:fixed; z-index:9999; pointer-events:none;
            background:#0f172a; color:#f8fafc;
            font-size:.7rem; padding:.3rem .6rem; border-radius:.375rem;
            white-space:nowrap; box-shadow:0 4px 12px rgba(0,0,0,.2);
            transition: opacity .15s;
        `;
        document.body.appendChild(tip);
    });

    document.addEventListener('mousemove', function (e) {
        if (!tip) return;
        tip.style.left = (e.clientX + 12) + 'px';
        tip.style.top  = (e.clientY - 28) + 'px';
    });

    document.addEventListener('mouseout', function (e) {
        if (tip && e.target.closest('[data-tooltip]')) {
            tip.remove();
            tip = null;
        }
    });
}

/* ============================================================
   13. CARD GRID / TABLE VIEW TOGGLE
   ============================================================ */
function initViewToggle() {
    document.querySelectorAll('[data-view-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const view     = btn.dataset.viewToggle; // 'table' | 'grid'
            const targetId = btn.dataset.viewTarget;
            const target   = document.getElementById(targetId);
            if (!target) return;

            // Update button states
            btn.closest('[data-view-group]')?.querySelectorAll('[data-view-toggle]').forEach(b => {
                b.classList.toggle('active', b === btn);
            });

            // Toggle view classes
            if (view === 'grid') {
                target.classList.add('view-grid');
                target.classList.remove('view-table');
            } else {
                target.classList.add('view-table');
                target.classList.remove('view-grid');
            }

            localStorage.setItem('erp_view_' + targetId, view);
        });
    });

    // Restore saved view
    document.querySelectorAll('[data-view-target]').forEach(function (btn) {
        const id    = btn.dataset.viewTarget;
        const saved = localStorage.getItem('erp_view_' + id);
        if (saved) {
            const trigger = document.querySelector(`[data-view-toggle="${saved}"][data-view-target="${id}"]`);
            if (trigger) trigger.click();
        }
    });
}

/* ============================================================
   14. AUTO-CLOSE ALERTS
   ============================================================ */
function initAutoCloseAlerts() {
    document.querySelectorAll('[data-auto-close]').forEach(function (el) {
        const ms = parseInt(el.dataset.autoClose) || 5000;
        setTimeout(function () {
            el.style.transition = 'opacity .3s';
            el.style.opacity    = '0';
            setTimeout(() => el.remove(), 350);
        }, ms);
    });
}

/* ============================================================
   15. ACTIVE NAV LINK HIGHLIGHT (fallback for JS-rendered routes)
   ============================================================ */
function highlightActiveNav() {
    const path = window.location.pathname;
    document.querySelectorAll('.nav-item[href]').forEach(function (link) {
        const href = link.getAttribute('href');
        if (!href || href === '#') return;
        const isActive = path === href || (href !== '/' && path.startsWith(href));
        link.classList.toggle('active', isActive);
    });
}

/* ============================================================
   INIT — runs when DOM is ready
   ============================================================ */
document.addEventListener('DOMContentLoaded', function () {
    restoreSidebarState();
    initAutoToasts();
    initTableSort();
    initTableSearch();
    initConfirmDelete();
    initTooltips();
    initViewToggle();
    initAutoCloseAlerts();
    highlightActiveNav();

    // Expose globals for Blade onclick usage
    window.toggleTheme          = toggleTheme;
    window.openSidebar          = openSidebar;
    window.closeSidebar         = closeSidebar;
    window.toggleSidebarCollapse= toggleSidebarCollapse;
    window.toggleNotifPanel     = toggleNotifPanel;
    window.toggleUserMenu       = toggleUserMenu;
    window.openCommandPalette   = openCommandPalette;
    window.closeCommandPalette  = closeCommandPalette;
    window.filterCmdResults     = filterCmdResults;
    window.cmdKeyNav            = cmdKeyNav;
    window.cmdRunAction         = cmdRunAction;
    window.toggleFab            = toggleFab;
    window.closeFab             = closeFab;
    window.openShortcutModal    = openShortcutModal;
    window.closeShortcutModal   = closeShortcutModal;
    window.showToast            = showToast;
    window.dismissToast         = dismissToast;
});
