/**
 * app.js — School ERP Modern UI (Module 20)
 * ─────────────────────────────────────────────
 * Clean unbundled version loaded directly by browser.
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
   2. SIDEBAR (mobile + desktop collapse)
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
        const np = document.getElementById('notif-panel');
        if (np) np.classList.add('hidden');
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function (e) {
    const nw = document.getElementById('notif-wrapper');
    const np = document.getElementById('notif-panel');
    if (np && nw && !nw.contains(e.target)) {
        np.classList.add('hidden');
    }
    const uw = document.getElementById('user-menu-wrapper');
    const um = document.getElementById('user-menu-panel');
    if (um && uw && !uw.contains(e.target)) {
        um.classList.add('hidden');
    }
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

function initAutoToasts() {
    document.querySelectorAll('.toast-auto-show').forEach(function (el) {
        const type = el.dataset.type || 'info';
        const msg  = el.dataset.msg || el.textContent;
        const title = el.dataset.title || '';
        showToast(msg, type, title);
        el.remove();
    });
}

/* ============================================================
   6. COMMAND PALETTE & GLOBAL SEARCH
   ============================================================ */
function getPinnedUrls() {
    try { return JSON.parse(localStorage.getItem('nshub_pinned_urls') || '[]'); } catch(e) { return []; }
}
function savePinnedUrls(urls) {
    try { localStorage.setItem('nshub_pinned_urls', JSON.stringify(urls)); } catch(e) {}
}
function getCustomShortcuts() {
    try { return JSON.parse(localStorage.getItem('nshub_custom_shortcuts') || '[]'); } catch(e) { return []; }
}
function saveCustomShortcuts(shortcuts) {
    try { localStorage.setItem('nshub_custom_shortcuts', JSON.stringify(shortcuts)); } catch(e) {}
}

function togglePinShortcut(cleanPath, label, e) {
    if (e) { e.preventDefault(); e.stopPropagation(); }
    let pinned = getPinnedUrls();
    if (pinned.includes(cleanPath)) {
        pinned = pinned.filter(p => p !== cleanPath);
        if (typeof showToast === 'function') showToast(`Unpinned "${label}" from shortcuts`, 'info');
    } else {
        pinned.push(cleanPath);
        if (typeof showToast === 'function') showToast(`Pinned "${label}" to shortcuts!`, 'success');
    }
    savePinnedUrls(pinned);
    const input = document.getElementById('cmd-input');
    renderCmdResults(input ? input.value : '');
}

function removeCustomShortcut(id, e) {
    if (e) { e.preventDefault(); e.stopPropagation(); }
    let customs = getCustomShortcuts();
    const target = customs.find(c => c.id === id);
    customs = customs.filter(c => c.id !== id);
    saveCustomShortcuts(customs);
    if (typeof showToast === 'function') showToast(`Removed custom shortcut "${target?.label || ''}"`, 'info');
    const input = document.getElementById('cmd-input');
    renderCmdResults(input ? input.value : '');
}

function openCreateShortcutModal() {
    const modal = document.getElementById('create-shortcut-modal');
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
    const form = document.getElementById('custom-shortcut-form');
    if (form) form.reset();
    const input = document.getElementById('shortcut-title');
    if (input) setTimeout(() => input.focus(), 50);
}

function closeCreateShortcutModal() {
    const modal = document.getElementById('create-shortcut-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.style.display = 'none';
    }
}

function selectShortcutIcon(btn, icon) {
    document.querySelectorAll('#shortcut-icon-picker .shortcut-icon-opt').forEach(b => {
        b.classList.remove('active', 'border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-950', 'text-indigo-600');
        b.classList.add('border-slate-200', 'dark:border-slate-700', 'text-slate-500');
    });
    if (btn) {
        btn.classList.add('active', 'border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-950', 'text-indigo-600');
        btn.classList.remove('border-slate-200', 'dark:border-slate-700', 'text-slate-500');
    }
    const hidden = document.getElementById('shortcut-selected-icon');
    if (hidden) hidden.value = icon;
}

function handleSaveCustomShortcut(e) {
    e.preventDefault();
    const title = (document.getElementById('shortcut-title')?.value || '').trim();
    let url = (document.getElementById('shortcut-url')?.value || '').trim();
    const category = (document.getElementById('shortcut-category')?.value || '').trim() || 'My Shortcuts';
    const key = (document.getElementById('shortcut-key')?.value || '').trim().toUpperCase();
    const icon = document.getElementById('shortcut-selected-icon')?.value || 'bi-star-fill';

    if (!title || !url) return;

    if (!url.startsWith('http://') && !url.startsWith('https://') && !url.startsWith('/') && !url.startsWith('#')) {
        url = '/' + url;
    }

    const newShortcut = {
        id: 'sc_' + Date.now(),
        label: title,
        href: url,
        category: category,
        icon: icon.includes('bi-') ? icon : 'bi-' + icon,
        shortcut: key || undefined,
        keywords: [title.toLowerCase(), category.toLowerCase(), 'shortcut', 'custom', key.toLowerCase()].filter(Boolean),
        isCustom: true
    };

    const customs = getCustomShortcuts();
    customs.unshift(newShortcut);
    saveCustomShortcuts(customs);

    closeCreateShortcutModal();
    if (typeof showToast === 'function') showToast(`Custom shortcut "${title}" created!`, 'success');
    
    const cmdInput = document.getElementById('cmd-input');
    renderCmdResults(cmdInput ? cmdInput.value : '');
}

function getActiveSidebarNavigation() {
    const items = [];
    const seenPaths = new Set();
    const pinnedUrls = getPinnedUrls();
    const sidebarNav = document.querySelector('.sidebar-nav') || document.querySelector('#sidebar .sidebar-scroll') || document.getElementById('sidebar');

    // 1. Incorporate User Created Custom Shortcuts first
    const customShortcuts = getCustomShortcuts();
    customShortcuts.forEach(sc => {
        if (!seenPaths.has(sc.href)) {
            seenPaths.add(sc.href);
            items.push({
                id: sc.id,
                label: sc.label,
                icon: sc.icon.includes('bi-') ? 'bi ' + sc.icon : 'bi bi-' + sc.icon,
                category: sc.category || '★ My Shortcuts',
                href: sc.href,
                shortcut: sc.shortcut,
                keywords: sc.keywords || [sc.label.toLowerCase()],
                isCustom: true,
                isPinned: pinnedUrls.includes(sc.href)
            });
        }
    });

    if (sidebarNav) {
        let currentCategory = 'Navigation';
        const elements = sidebarNav.querySelectorAll('.sidebar-section-label, a.sidebar-link, a[href]');
        elements.forEach(el => {
            if (el.classList.contains('sidebar-section-label') || el.tagName === 'P') {
                const clone = el.cloneNode(true);
                clone.querySelectorAll('.sidebar-badge, .badge, span').forEach(b => b.remove());
                const catText = clone.textContent.replace(/\s+/g, ' ').trim();
                if (catText && !catText.includes('Impersonating')) {
                    currentCategory = catText;
                }
            } else if (el.tagName === 'A') {
                const href = el.getAttribute('href');
                if (!href || href === '#' || href.startsWith('javascript:')) return;

                let cleanPath = href;
                try {
                    const parsed = new URL(href, window.location.origin);
                    cleanPath = parsed.pathname + (parsed.search || '');
                } catch(e) {}

                if (cleanPath.includes('logout') || seenPaths.has(cleanPath)) return;

                const textEl = el.querySelector('.sidebar-link-text') || el;
                const clone = textEl.cloneNode(true);
                clone.querySelectorAll('.sidebar-badge, .badge, span[class*="badge"]').forEach(b => b.remove());
                const label = clone.textContent.replace(/\s+/g, ' ').trim();

                if (!label || label.toLowerCase().includes('nepalschoolhub')) return;
                seenPaths.add(cleanPath);

                const iconEl = el.querySelector('.sidebar-link-icon i, i');
                const icon = iconEl ? iconEl.className : 'bi bi-grid-fill';

                const words = label.toLowerCase().split(/[\s—\-\&/,()]+/).filter(w => w.length > 1);
                const catWords = currentCategory.toLowerCase().split(/[\s—\-\&/,()]+/).filter(w => w.length > 1);
                const keywords = Array.from(new Set([...words, ...catWords, label.toLowerCase(), currentCategory.toLowerCase()]));

                const lLower = label.toLowerCase();
                const cLower = currentCategory.toLowerCase();

                if (lLower.includes('class') || lLower.includes('section') || cLower.includes('academic')) {
                    keywords.push('class', 'classes', 'section', 'sections', 'grade', 'grades', 'classroom', 'routine', 'timetable');
                }
                if (lLower.includes('course') || lLower.includes('subject') || lLower.includes('curriculum') || lLower.includes('syllabus')) {
                    keywords.push('course', 'courses', 'subject', 'subjects', 'curriculum', 'syllabus', 'book', 'theory', 'practical', 'study');
                }
                if (lLower.includes('homework') || lLower.includes('assignment') || lLower.includes('project')) {
                    keywords.push('homework', 'hw', 'assignment', 'assignments', 'project', 'projects', 'tasks', 'submissions');
                }
                if (lLower.includes('exam') || lLower.includes('mark') || lLower.includes('result') || lLower.includes('grade') || lLower.includes('assessment')) {
                    keywords.push('exam', 'exams', 'marks', 'mark', 'result', 'results', 'marksheet', 'report card', 'grade', 'gradebook', 'test', 'quiz');
                }
                if (lLower.includes('fee') || lLower.includes('payment') || lLower.includes('invoice') || lLower.includes('finance') || lLower.includes('salary') || lLower.includes('expense') || lLower.includes('income') || lLower.includes('payroll')) {
                    keywords.push('fee', 'fees', 'invoice', 'invoices', 'payment', 'payments', 'dues', 'receipt', 'finance', 'salary', 'payroll', 'expense', 'income', 'qr', 'bill');
                }
                if (lLower.includes('attend')) {
                    keywords.push('attendance', 'present', 'absent', 'roll call', 'rfid', 'daily attendance');
                }
                if (lLower.includes('student') || lLower.includes('admiss') || lLower.includes('alumni') || lLower.includes('child')) {
                    keywords.push('student', 'students', 'admission', 'admissions', 'enquiry', 'inquiry', 'children', 'child', 'alumni', 'transfer', 'tc', 'roll');
                }
                if (lLower.includes('teach') || lLower.includes('staff') || lLower.includes('faculty') || lLower.includes('hr')) {
                    keywords.push('teacher', 'teachers', 'staff', 'faculty', 'employee', 'hr', 'instructor', 'leave');
                }
                if (lLower.includes('routine') || lLower.includes('timetable') || lLower.includes('schedule')) {
                    keywords.push('routine', 'timetable', 'schedule', 'periods', 'time table', 'calendar');
                }
                if (lLower.includes('notice') || lLower.includes('circular') || lLower.includes('broadcast') || lLower.includes('message')) {
                    keywords.push('notice', 'notices', 'circular', 'announcements', 'broadcast', 'sms', 'message', 'news');
                }
                if (lLower.includes('librar') || lLower.includes('book')) {
                    keywords.push('library', 'books', 'book catalog', 'issue book', 'return book');
                }
                if (lLower.includes('transport') || lLower.includes('bus') || lLower.includes('vehicle')) {
                    keywords.push('transport', 'bus', 'van', 'fleet', 'vehicles', 'routes', 'tracking');
                }
                if (lLower.includes('hostel') || lLower.includes('dormitory')) {
                    keywords.push('hostel', 'dormitory', 'rooms', 'beds', 'room allocation');
                }
                if (lLower.includes('note') || lLower.includes('material') || lLower.includes('online')) {
                    keywords.push('study notes', 'materials', 'online classes', 'online exams', 'video class', 'resources');
                }
                if (lLower.includes('quiz')) {
                    keywords.push('quiz', 'live quiz', 'trivia', 'game', 'arena');
                }

                const isPinned = pinnedUrls.includes(cleanPath);

                items.push({
                    label: label,
                    icon: icon,
                    category: currentCategory,
                    href: href,
                    cleanPath: cleanPath,
                    keywords: keywords,
                    isPinned: isPinned
                });
            }
        });
    }

    // Add System Actions
    items.push(
        { label: 'Create New Shortcut', icon: 'bi bi-plus-circle-fill text-indigo-500', category: 'Actions', action: 'createShortcut', keywords: ['add shortcut', 'new shortcut', 'create shortcut', 'custom shortcut', 'bookmark'], shortcut: 'N' },
        { label: 'Toggle Dark / Light Mode', icon: 'bi bi-moon-stars-fill', category: 'Actions', action: 'toggleTheme', keywords: ['dark mode', 'light mode', 'theme', 'night mode'], shortcut: 'T' },
        { label: 'Change Password', icon: 'bi bi-key-fill', category: 'Actions', href: '/password/change', keywords: ['change password', 'reset password', 'security credentials', 'password'] },
        { label: 'Keyboard Shortcuts Help', icon: 'bi bi-keyboard-fill', category: 'Actions', action: 'showShortcuts', keywords: ['shortcuts', 'hotkeys', 'keyboard help', 'help'], shortcut: '?' }
    );

    return items;
}

let cmdSelected = -1;
let publicSearchDebounceTimer = null;
let publicSearchAbortController = null;

function openCommandPalette() {
    const backdrop = document.getElementById('cmd-palette-backdrop');
    const input    = document.getElementById('cmd-input');
    if (!backdrop) return;
    backdrop.classList.add('is-open');
    backdrop.style.display = 'flex';
    if (input) {
        input.value = '';
        setTimeout(() => input.focus(), 50);
    }
    renderCmdResults('');
    cmdSelected = -1;
}

function closeCommandPalette() {
    const backdrop = document.getElementById('cmd-palette-backdrop');
    if (backdrop) {
        backdrop.classList.remove('is-open');
        backdrop.style.display = 'none';
    }
    cmdSelected = -1;
    if (publicSearchAbortController) {
        try { publicSearchAbortController.abort(); } catch(e) {}
    }
}

function highlight(text, query) {
    if (!query) return text;
    const re = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
    return String(text).replace(re, '<mark class="bg-amber-200 text-slate-900 rounded-sm px-0.5">$1</mark>');
}

function renderResultsListPublic(matched, dbItems, q, container) {
    let html = '';

    // 1. Sidebar Navigation Items & Shortcuts
    if (matched.length > 0) {
        html += `<div class="cmd-group-label">Pages & Shortcuts (${matched.length})</div>`;
        matched.forEach((c, i) => {
            const label = highlight(c.label, q);
            const shortcutBadge = c.shortcut ? `<span class="cmd-badge font-mono">${c.shortcut}</span>` : '';
            const tag = c.href ? 'a' : 'button';
            const hrefAttr = c.href ? `href="${c.href}"` : '';
            const actionAttr = c.action ? `onclick="cmdRunAction('${c.action}')"` : '';
            const iconClass = c.icon ? (c.icon.includes('bi-') ? c.icon : 'bi ' + c.icon) : 'bi bi-grid-fill';
            const pinClass = c.isPinned ? 'is-pinned text-amber-500' : 'text-slate-400 hover:text-amber-500';
            const pinIcon = c.isPinned ? 'bi-star-fill' : 'bi-star';
            const pinPath = c.cleanPath || c.href || '';
            
            let pinBtn = '';
            if (c.href) {
                pinBtn = `<button type="button" class="cmd-pin-btn ${pinClass}" data-pin-path="${pinPath}" onclick="togglePinShortcut(this.getAttribute('data-pin-path'), this.closest('.cmd-result-item')?.querySelector('.cmd-result-title span')?.textContent || '', event)" title="${c.isPinned ? 'Unpin from shortcuts' : 'Pin to shortcuts'}">
                    <i class="bi ${pinIcon}"></i>
                </button>`;
            }

            let deleteBtn = '';
            if (c.isCustom && c.id) {
                deleteBtn = `<button type="button" class="cmd-delete-btn" data-del-id="${c.id}" onclick="removeCustomShortcut(this.getAttribute('data-del-id'), event)" title="Delete custom shortcut">
                    <i class="bi bi-trash"></i>
                </button>`;
            }

            html += `<${tag} class="cmd-result-item" ${hrefAttr} ${actionAttr} data-cmd-idx="${i}">
                <span class="cmd-icon-box"><i class="${iconClass}"></i></span>
                <div class="cmd-result-text">
                    <div class="cmd-result-title flex items-center gap-1.5">
                        <span>${label}</span>
                        ${c.isCustom ? '<span class="text-[9px] px-1 py-0.2 bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 rounded font-bold uppercase">Custom</span>' : ''}
                    </div>
                    <div class="cmd-result-subtitle">${c.category}</div>
                </div>
                <div class="cmd-item-actions">
                    ${shortcutBadge}
                    ${pinBtn}
                    ${deleteBtn}
                </div>
            </${tag}>`;
        });
    }

    // 2. Database Records
    if (dbItems && dbItems.length > 0) {
        html += `<div class="cmd-group-label" style="color:#2563eb;">Database Records (${dbItems.length})</div>`;
        dbItems.forEach((item, i) => {
            const label = highlight(item.label, q);
            const sublabel = highlight(item.sublabel, q);
            const iconClass = item.icon ? (item.icon.includes('bi-') ? item.icon : 'bi ' + item.icon) : 'bi bi-bookmark-fill';
            const globalIdx = matched.length + i;
            html += `<a class="cmd-result-item" href="${item.href}" data-cmd-idx="${globalIdx}">
                <span class="cmd-icon-box" style="background:#eff6ff;color:#2563eb;"><i class="${iconClass}"></i></span>
                <div class="cmd-result-text">
                    <div class="cmd-result-title">${label}</div>
                    <div class="cmd-result-subtitle">${sublabel}</div>
                </div>
            </a>`;
        });
    }

    if (!html) {
        html = `<div style="padding:2.5rem 1rem; text-align:center; color:#94a3b8; font-size:0.875rem;">
            <i class="bi bi-search" style="display:block; font-size:1.75rem; margin-bottom:0.5rem; opacity:0.6;"></i>
            <p>No results found for "${q}"</p>
            <button type="button" onclick="openCreateShortcutModal()" class="mt-2 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                + Create a custom shortcut for "${q}"
            </button>
        </div>`;
    }

    container.innerHTML = html;
    cmdSelected = -1;
}

function renderInitialGroupedPublic(matched, container) {
    if (!matched.length) {
        container.innerHTML = `<div style="padding:2rem 1rem; text-align:center; color:#94a3b8;">
            <p>No navigation items found.</p>
            <button type="button" onclick="openCreateShortcutModal()" class="mt-2 px-3 py-1.5 bg-indigo-600 text-white text-xs font-bold rounded-xl shadow-xs">
                + Create Custom Shortcut
            </button>
        </div>`;
        return;
    }

    const pinnedList = matched.filter(c => c.isPinned && !c.isCustom);
    const customList = matched.filter(c => c.isCustom);
    const regularList = matched.filter(c => !c.isPinned && !c.isCustom);

    const groups = {};

    if (pinnedList.length > 0) {
        groups['★ Pinned Shortcuts'] = pinnedList;
    }
    if (customList.length > 0) {
        groups['★ My Custom Shortcuts'] = customList;
    }

    regularList.forEach(c => {
        if (!groups[c.category]) groups[c.category] = [];
        groups[c.category].push(c);
    });

    let html = '';
    let totalIdx = 0;
    Object.keys(groups).forEach(cat => {
        const isSpecialGroup = cat.startsWith('★');
        const groupColor = isSpecialGroup ? 'color:#f59e0b;font-weight:700;' : '';
        html += `<div class="cmd-group-label" style="${groupColor}">${cat} (${groups[cat].length})</div>`;
        groups[cat].forEach((c) => {
            const shortcutBadge = c.shortcut ? `<span class="cmd-badge font-mono">${c.shortcut}</span>` : '';
            const tag = c.href ? 'a' : 'button';
            const hrefAttr = c.href ? `href="${c.href}"` : '';
            const actionAttr = c.action ? `onclick="cmdRunAction('${c.action}')"` : '';
            const iconClass = c.icon ? (c.icon.includes('bi-') ? c.icon : 'bi ' + c.icon) : 'bi bi-grid-fill';
            const pinClass = c.isPinned ? 'is-pinned text-amber-500' : 'text-slate-400 hover:text-amber-500';
            const pinIcon = c.isPinned ? 'bi-star-fill' : 'bi-star';
            const pinPath = c.cleanPath || c.href || '';
            
            let pinBtn = '';
            if (c.href) {
                pinBtn = `<button type="button" class="cmd-pin-btn ${pinClass}" data-pin-path="${pinPath}" onclick="togglePinShortcut(this.getAttribute('data-pin-path'), this.closest('.cmd-result-item')?.querySelector('.cmd-result-title span')?.textContent || '', event)" title="${c.isPinned ? 'Unpin from shortcuts' : 'Pin to shortcuts'}">
                    <i class="bi ${pinIcon}"></i>
                </button>`;
            }

            let deleteBtn = '';
            if (c.isCustom && c.id) {
                deleteBtn = `<button type="button" class="cmd-delete-btn" data-del-id="${c.id}" onclick="removeCustomShortcut(this.getAttribute('data-del-id'), event)" title="Delete custom shortcut">
                    <i class="bi bi-trash"></i>
                </button>`;
            }

            html += `<${tag} class="cmd-result-item" ${hrefAttr} ${actionAttr} data-cmd-idx="${totalIdx++}">
                <span class="cmd-icon-box"><i class="${iconClass}"></i></span>
                <div class="cmd-result-text">
                    <div class="cmd-result-title flex items-center gap-1.5">
                        <span>${c.label}</span>
                        ${c.isCustom ? '<span class="text-[9px] px-1 py-0.2 bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 rounded font-bold uppercase">Custom</span>' : ''}
                    </div>
                    <div class="cmd-result-subtitle">${c.category}</div>
                </div>
                <div class="cmd-item-actions">
                    ${shortcutBadge}
                    ${pinBtn}
                    ${deleteBtn}
                </div>
            </${tag}>`;
        });
    });

    container.innerHTML = html;
    cmdSelected = -1;
}

function renderCmdResults(query) {
    const container = document.getElementById('cmd-results');
    if (!container) return;

    const q = (query || '').trim().toLowerCase();
    const liveItems = getActiveSidebarNavigation();

    if (!q) {
        renderInitialGroupedPublic(liveItems, container);
        return;
    }

    const qWords = q.split(/\s+/).filter(Boolean);
    const matched = liveItems.filter(c => {
        const labelLower = (c.label || '').toLowerCase();
        const catLower = (c.category || '').toLowerCase();
        const kwList = Array.isArray(c.keywords) ? c.keywords : [];

        // Full query matches label or category directly
        if (labelLower.includes(q) || catLower.includes(q)) return true;

        // Shortcut key match
        if (c.shortcut && c.shortcut.toLowerCase() === q) return true;

        // Every word in search query matches either label, category, or a keyword
        return qWords.every(word => {
            if (labelLower.includes(word) || catLower.includes(word)) return true;
            return kwList.some(k => k.toLowerCase().includes(word));
        });
    });

    // 1. Instant local render
    renderResultsListPublic(matched, [], q, container);

    // 2. Debounced dynamic fetch
    if (publicSearchDebounceTimer) clearTimeout(publicSearchDebounceTimer);
    if (publicSearchAbortController) {
        try { publicSearchAbortController.abort(); } catch(e) {}
    }
    publicSearchAbortController = new AbortController();

    publicSearchDebounceTimer = setTimeout(() => {
        fetch(`/search?q=${encodeURIComponent(q)}`, {
            signal: publicSearchAbortController.signal,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.ok ? r.json() : { data: [] })
        .then(response => {
            const dbItems = response.data || [];
            renderResultsListPublic(matched, dbItems, q, container);
        })
        .catch(err => {
            if (err.name !== 'AbortError') {
                renderResultsListPublic(matched, [], q, container);
            }
        });
    }, 150);
}

function filterCmdResults(val) {
    renderCmdResults(val);
}

function cmdRunAction(action) {
    closeCommandPalette();
    if (action === 'createShortcut') openCreateShortcutModal();
    if (action === 'toggleTheme')    toggleTheme();
    if (action === 'showShortcuts')  openShortcutModal();
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
        const target = (cmdSelected >= 0 && items[cmdSelected]) ? items[cmdSelected] : items[0];
        if (target) {
            if (target.getAttribute('href')) {
                window.location.href = target.getAttribute('href');
            } else {
                target.click();
            }
        }
        closeCommandPalette();
        return;
    } else if (e.key === 'Escape') {
        closeCommandPalette();
        return;
    }

    items.forEach((el, idx) => el.classList.toggle('is-selected', idx === cmdSelected));
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

            table.querySelectorAll('th').forEach(h => {
                h.classList.remove('sort-asc', 'sort-desc');
                const si = h.querySelector('.sort-icon');
                if (si) si.textContent = '';
            });

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
   11. CONFIRM-DELETE HELPER (data-confirm attribute)
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
   12. TOOLTIP (data-tooltip attribute → simple tooltip)
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
            const view     = btn.dataset.viewToggle;
            const targetId = btn.dataset.viewTarget;
            const target   = document.getElementById(targetId);
            if (!target) return;

            btn.closest('[data-view-group]')?.querySelectorAll('[data-view-toggle]').forEach(b => {
                b.classList.toggle('active', b === btn);
            });

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
   15. ACTIVE NAV LINK HIGHLIGHT
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
   16. UNIVERSAL MOBILE RESPONSIVE TABLES
   ============================================================ */
function initResponsiveTables() {
    document.querySelectorAll('table').forEach(function (table) {
        if (!table.closest('.overflow-x-auto, .table-responsive, [class*="overflow-x"]')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive-wrapper overflow-x-auto w-full max-w-full';
            wrapper.style.webkitOverflowScrolling = 'touch';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
    });
}

/* ============================================================
   INIT & EXPORTS
   ============================================================ */
// Expose immediately so onclick handlers work without waiting for DOMContentLoaded
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
window.openCreateShortcutModal = openCreateShortcutModal;
window.closeCreateShortcutModal= closeCreateShortcutModal;
window.selectShortcutIcon   = selectShortcutIcon;
window.handleSaveCustomShortcut = handleSaveCustomShortcut;
window.togglePinShortcut    = togglePinShortcut;
window.removeCustomShortcut = removeCustomShortcut;
window.getPinnedUrls        = getPinnedUrls;
window.getCustomShortcuts   = getCustomShortcuts;
window.showToast            = showToast;
window.dismissToast         = dismissToast;

document.addEventListener('DOMContentLoaded', function () {
    restoreSidebarState();
    initAutoToasts();
    initResponsiveTables();
    initTableSort();
    initTableSearch();
    initConfirmDelete();
    initTooltips();
    initViewToggle();
    initAutoCloseAlerts();
    highlightActiveNav();
});