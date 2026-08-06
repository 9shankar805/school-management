{{-- FullCalendar v6 assets (replaces v5.11.3) --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">

{{-- Calendar wrapper --}}
<div id="fc-wrapper" class="rounded-2xl overflow-hidden border border-slate-200" style="background:#fff">
    <div id="calendar-el"></div>
</div>

{{-- ── Event Detail / View Modal ─────────────────────────────────────── --}}
<div id="event-view-modal" class="fixed inset-0 z-[300] hidden items-center justify-center p-4" style="background:rgba(15,23,42,0.55);backdrop-filter:blur(4px)">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <div id="evm-header" class="px-6 py-4 flex items-center justify-between" style="background:#6366f1">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="bi bi-calendar-event text-white text-sm"></i>
                </span>
                <h3 id="evm-title" class="font-bold text-white text-base truncate max-w-[240px]"></h3>
            </div>
            <button onclick="closeEventViewModal()" class="w-7 h-7 rounded-lg bg-white/20 hover:bg-white/30 flex items-center justify-center transition">
                <i class="bi bi-x text-white text-lg leading-none"></i>
            </button>
        </div>
        <div class="px-6 py-5 space-y-3">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
                    <i class="bi bi-calendar3 text-slate-500 text-xs"></i>
                </span>
                <div>
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide">Date &amp; Time</p>
                    <p id="evm-date" class="text-sm font-medium text-slate-700 mt-0.5"></p>
                </div>
            </div>
            <div id="evm-desc-row" class="flex items-start gap-3 hidden">
                <span class="mt-0.5 w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
                    <i class="bi bi-card-text text-slate-500 text-xs"></i>
                </span>
                <div>
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide">Description</p>
                    <p id="evm-desc" class="text-sm text-slate-600 mt-0.5"></p>
                </div>
            </div>
        </div>
        <div id="evm-actions" class="px-6 pb-5 flex gap-2 justify-end hidden">
            <button id="evm-edit-btn" onclick="openEditFromView()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
                <i class="bi bi-pencil"></i> Edit
            </button>
            <button id="evm-delete-btn" onclick="deleteEventFromView()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold bg-rose-50 text-rose-600 hover:bg-rose-100 transition">
                <i class="bi bi-trash3"></i> Delete
            </button>
        </div>
    </div>
</div>

{{-- ── Create / Edit Event Modal ─────────────────────────────────────── --}}
<div id="event-form-modal" class="fixed inset-0 z-[300] hidden items-center justify-center p-4" style="background:rgba(15,23,42,0.55);backdrop-filter:blur(4px)">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        {{-- Modal header --}}
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-indigo-100 flex items-center justify-center">
                    <i class="bi bi-calendar-plus text-indigo-600 text-sm"></i>
                </span>
                <h3 id="efm-title" class="font-bold text-slate-800 text-base">New Event</h3>
            </div>
            <button onclick="closeEventFormModal()" class="w-7 h-7 rounded-lg hover:bg-slate-100 flex items-center justify-center transition text-slate-400 hover:text-slate-600">
                <i class="bi bi-x text-lg leading-none"></i>
            </button>
        </div>

        {{-- Form body --}}
        <form id="event-form" onsubmit="submitEventForm(event)" class="px-6 py-5 space-y-4">
            <input type="hidden" id="efm-event-id">
            <input type="hidden" id="efm-mode" value="create">

            {{-- Title --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Event Title <span class="text-rose-500">*</span></label>
                <input id="efm-event-title" type="text" placeholder="e.g. Annual Sports Day"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 transition" required>
            </div>

            {{-- Date range --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Start Date <span class="text-rose-500">*</span></label>
                    <input id="efm-start" type="datetime-local"
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 transition" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">End Date</label>
                    <input id="efm-end" type="datetime-local"
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 transition">
                </div>
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Description</label>
                <textarea id="efm-description" rows="2" placeholder="Optional note about this event…"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 transition resize-none"></textarea>
            </div>

            {{-- Color picker --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2">Color</label>
                <div class="flex items-center gap-2 flex-wrap">
                    @foreach(['#6366f1','#2563eb','#0891b2','#059669','#16a34a','#ca8a04','#ea580c','#dc2626','#9333ea','#db2777','#475569'] as $clr)
                    <button type="button" onclick="selectEventColor('{{ $clr }}')"
                        data-color="{{ $clr }}"
                        class="color-swatch w-7 h-7 rounded-full border-2 border-transparent transition hover:scale-110 focus:outline-none"
                        style="background:{{ $clr }}"></button>
                    @endforeach
                    <input id="efm-color-custom" type="color" value="#6366f1"
                        onchange="selectEventColor(this.value)"
                        class="w-7 h-7 rounded-full border border-slate-200 cursor-pointer p-0 overflow-hidden"
                        title="Custom color">
                </div>
                <input type="hidden" id="efm-color" value="#6366f1">
            </div>

            {{-- Submit --}}
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="closeEventFormModal()"
                    class="flex-1 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit" id="efm-submit-btn"
                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white transition"
                    style="background:#6366f1">
                    Save Event
                </button>
            </div>
        </form>
    </div>
</div>

{{-- FullCalendar v6 JS --}}
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<style>
/* ── FullCalendar v6 overrides ──────────────────────────────────── */
#fc-wrapper .fc { font-family: inherit; }
#fc-wrapper .fc-toolbar.fc-header-toolbar { padding: 1.25rem 1.5rem 0; margin-bottom: 1rem; }
#fc-wrapper .fc-toolbar-title { font-size: 1.05rem; font-weight: 700; color: #1e293b; }
#fc-wrapper .fc-button { border-radius: 0.625rem !important; font-size: 0.75rem !important; font-weight: 600 !important; padding: 0.4rem 0.85rem !important; text-transform: capitalize !important; }
#fc-wrapper .fc-button-primary { background: #6366f1 !important; border-color: #6366f1 !important; color: #fff !important; }
#fc-wrapper .fc-button-primary:not(.fc-button-active):hover { background: #4f46e5 !important; border-color: #4f46e5 !important; }
#fc-wrapper .fc-button-primary.fc-button-active { background: #4338ca !important; border-color: #4338ca !important; box-shadow: inset 0 2px 4px rgba(0,0,0,.15) !important; }
#fc-wrapper .fc-button-group { gap: 0.25rem; }
#fc-wrapper .fc-daygrid-day { cursor: pointer; }
#fc-wrapper .fc-daygrid-day:hover { background: #f8fafc; }
#fc-wrapper .fc-daygrid-day-number { font-size: 0.8rem; font-weight: 500; color: #475569; padding: 0.35rem 0.5rem; }
#fc-wrapper .fc-col-header-cell { background: #f8fafc; }
#fc-wrapper .fc-col-header-cell-cushion { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; padding: 0.5rem 0; text-decoration: none; }
#fc-wrapper .fc-daygrid-day.fc-day-today { background: #eef2ff !important; }
#fc-wrapper .fc-daygrid-day.fc-day-today .fc-daygrid-day-number { color: #6366f1; font-weight: 700; }
#fc-wrapper .fc-event { border-radius: 6px !important; border: none !important; font-size: 0.72rem !important; font-weight: 600 !important; padding: 2px 6px !important; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,.15); transition: transform .1s, box-shadow .1s; }
#fc-wrapper .fc-event:hover { transform: translateY(-1px); box-shadow: 0 3px 8px rgba(0,0,0,.2); }
#fc-wrapper .fc-more-link { font-size: 0.68rem; font-weight: 700; color: #6366f1; padding: 0 4px; }
#fc-wrapper .fc-highlight { background: #e0e7ff !important; border-radius: 4px; }
#fc-wrapper .fc-timegrid-slot-label { font-size: 0.7rem; color: #94a3b8; }
#fc-wrapper .fc-scrollgrid { border-radius: 0 !important; border: none !important; }
#fc-wrapper .fc-scrollgrid td, #fc-wrapper .fc-scrollgrid th { border-color: #f1f5f9 !important; }
#fc-wrapper td.fc-day-sat .fc-daygrid-day-number,
#fc-wrapper td.fc-day-sun .fc-daygrid-day-number { color: #e11d48; }

/* Modal helpers */
.modal-show { display: flex !important; animation: modalIn .18s ease; }
@keyframes modalIn { from { opacity:0; transform:scale(.96) } to { opacity:1; transform:scale(1) } }
.color-swatch.active { border-color: #1e293b !important; box-shadow: 0 0 0 2px #fff, 0 0 0 4px #1e293b; transform: scale(1.15); }
</style>

<script>
(function () {
    'use strict';

    var SITEURL = "{{ url('/') }}";
    var CSRF    = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var IS_EDITABLE = {{ ($editable ?? 'false') === 'true' ? 'true' : 'false' }};

    var calendarEl = document.getElementById('calendar-el');
    var calendar;
    var viewingEvent = null; // holds the FC event object for the view modal

    // ── Helper: format datetime-local value ────────────────────────────
    function toInputDate(str) {
        if (!str) return '';
        var d = new Date(str);
        if (isNaN(d)) return '';
        var pad = function(n){ return String(n).padStart(2,'0'); };
        return d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate())+'T'+pad(d.getHours())+':'+pad(d.getMinutes());
    }

    function formatDisplayDate(start, end) {
        var opts = { weekday:'short', month:'short', day:'numeric', year:'numeric' };
        var s = new Date(start);
        var out = s.toLocaleDateString('en-US', opts);
        if (start && start.includes('T') && !start.endsWith('T00:00:00')) {
            out += ' · ' + s.toLocaleTimeString('en-US', {hour:'2-digit', minute:'2-digit'});
        }
        if (end) {
            var e = new Date(end);
            if (!isNaN(e) && e > s) {
                out += ' → ' + e.toLocaleDateString('en-US', {month:'short', day:'numeric'});
                if (end.includes('T') && !end.endsWith('T00:00:00')) {
                    out += ' ' + e.toLocaleTimeString('en-US', {hour:'2-digit', minute:'2-digit'});
                }
            }
        }
        return out;
    }

    // ── Colour swatch selection ─────────────────────────────────────────
    window.selectEventColor = function (color) {
        document.getElementById('efm-color').value = color;
        document.getElementById('efm-submit-btn').style.background = color;
        document.querySelectorAll('.color-swatch').forEach(function (sw) {
            sw.classList.toggle('active', sw.dataset.color === color);
        });
        var custom = document.getElementById('efm-color-custom');
        if (custom) custom.value = color;
    };

    // ── Open / close form modal ─────────────────────────────────────────
    function openEventFormModal(mode, prefillStart, prefillEnd) {
        document.getElementById('efm-mode').value = mode || 'create';
        document.getElementById('efm-title').textContent = (mode === 'edit') ? 'Edit Event' : 'New Event';
        document.getElementById('efm-start').value = prefillStart || '';
        document.getElementById('efm-end').value   = prefillEnd   || '';
        document.getElementById('efm-event-title').value = '';
        document.getElementById('efm-description').value = '';
        selectEventColor('#6366f1');
        var modal = document.getElementById('event-form-modal');
        modal.classList.add('modal-show');
        setTimeout(function(){ document.getElementById('efm-event-title').focus(); }, 80);
    }

    window.closeEventFormModal = function () {
        document.getElementById('event-form-modal').classList.remove('modal-show');
    };

    // ── Open / close view modal ─────────────────────────────────────────
    function openEventViewModal(fcEvent) {
        viewingEvent = fcEvent;
        var color = fcEvent.backgroundColor || '#6366f1';
        document.getElementById('evm-header').style.background = color;
        document.getElementById('evm-title').textContent = fcEvent.title;
        document.getElementById('evm-date').textContent  = formatDisplayDate(fcEvent.startStr, fcEvent.endStr);

        var desc = fcEvent.extendedProps && fcEvent.extendedProps.description;
        var descRow = document.getElementById('evm-desc-row');
        if (desc) {
            document.getElementById('evm-desc').textContent = desc;
            descRow.classList.remove('hidden');
        } else {
            descRow.classList.add('hidden');
        }

        var actionsEl = document.getElementById('evm-actions');
        if (IS_EDITABLE) {
            actionsEl.classList.remove('hidden');
        } else {
            actionsEl.classList.add('hidden');
        }

        document.getElementById('event-view-modal').classList.add('modal-show');
    }

    window.closeEventViewModal = function () {
        document.getElementById('event-view-modal').classList.remove('modal-show');
        viewingEvent = null;
    };

    // ── Edit from view modal ─────────────────────────────────────────────
    window.openEditFromView = function () {
        if (!viewingEvent) return;
        closeEventViewModal();
        // Pre-fill form for editing
        document.getElementById('efm-mode').value = 'edit';
        document.getElementById('efm-title').textContent = 'Edit Event';
        document.getElementById('efm-event-id').value = viewingEvent.id;
        document.getElementById('efm-event-title').value = viewingEvent.title;
        document.getElementById('efm-start').value = toInputDate(viewingEvent.startStr);
        document.getElementById('efm-end').value   = toInputDate(viewingEvent.endStr);
        document.getElementById('efm-description').value = (viewingEvent.extendedProps && viewingEvent.extendedProps.description) || '';
        selectEventColor(viewingEvent.backgroundColor || '#6366f1');
        document.getElementById('event-form-modal').classList.add('modal-show');
        setTimeout(function(){ document.getElementById('efm-event-title').focus(); }, 80);
    };

    // ── Delete from view modal ───────────────────────────────────────────
    window.deleteEventFromView = function () {
        if (!viewingEvent) return;
        var id = viewingEvent.id;
        closeEventViewModal();
        showConfirmToast('Delete "' + viewingEvent.title + '"?', function () {
            fetch(SITEURL + '/calendar-crud-ajax', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ id: id, type: 'delete' })
            }).then(function (r) { return r.json(); }).then(function () {
                var ev = calendar.getEventById(id);
                if (ev) ev.remove();
                showToast('Event deleted', 'success');
            });
        });
    };

    // ── Form submit (create / edit) ──────────────────────────────────────
    window.submitEventForm = function (e) {
        e.preventDefault();
        var mode  = document.getElementById('efm-mode').value;
        var id    = document.getElementById('efm-event-id').value;
        var title = document.getElementById('efm-event-title').value.trim();
        var start = document.getElementById('efm-start').value;
        var end   = document.getElementById('efm-end').value;
        var color = document.getElementById('efm-color').value;
        var desc  = document.getElementById('efm-description').value.trim();

        if (!title || !start) return;

        var payload = { type: mode, title: title, start: start, end: end || start, color: color, description: desc };
        if (mode === 'edit') payload.id = id;

        var btn = document.getElementById('efm-submit-btn');
        btn.disabled = true;
        btn.textContent = 'Saving…';

        fetch(SITEURL + '/calendar-crud-ajax', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(payload)
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            closeEventFormModal();
            if (mode === 'create') {
                calendar.addEvent({
                    id: data.id,
                    title: data.title,
                    start: data.start,
                    end: data.end,
                    backgroundColor: data.backgroundColor,
                    borderColor: data.borderColor,
                    textColor: '#ffffff',
                    extendedProps: { description: desc }
                });
                showToast('Event created!', 'success');
            } else {
                var ev = calendar.getEventById(id);
                if (ev) {
                    ev.setProp('title', title);
                    ev.setStart(start);
                    ev.setEnd(end || start);
                    ev.setProp('backgroundColor', color);
                    ev.setProp('borderColor', color);
                }
                showToast('Event updated!', 'success');
            }
        })
        .catch(function () {
            showToast('Something went wrong. Please try again.', 'error');
        })
        .finally(function () {
            btn.disabled = false;
            btn.textContent = 'Save Event';
        });
    };

    // ── Close modals on backdrop click ───────────────────────────────────
    ['event-view-modal', 'event-form-modal'].forEach(function (id) {
        document.getElementById(id).addEventListener('click', function (e) {
            if (e.target === this) {
                this.classList.remove('modal-show');
            }
        });
    });

    // ── Confirm toast helper ─────────────────────────────────────────────
    function showConfirmToast(msg, onConfirm) {
        if (typeof showToast === 'function') {
            if (confirm(msg)) onConfirm();
        } else {
            if (confirm(msg)) onConfirm();
        }
    }

    // ── Initialise FullCalendar ──────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left:   'prev,next today',
                center: 'title',
                right:  'dayGridMonth,timeGridWeek,listWeek'
            },
            buttonText: {
                today: 'Today',
                month: 'Month',
                week:  'Week',
                list:  'List',
            },
            height: {{ ($editable ?? 'false') === 'true' ? 600 : 'auto' }},
            editable: IS_EDITABLE,
            selectable: IS_EDITABLE,
            eventMaxStack: 3,
            navLinks: true,
            dayMaxEvents: 3,
            events: {
                url: SITEURL + '/calendar-event',
                method: 'GET',
                failure: function () {
                    showToast('Could not load events.', 'error');
                }
            },
            // Select date range → open create modal
            select: function (info) {
                if (!IS_EDITABLE) return;
                document.getElementById('efm-event-id').value = '';
                openEventFormModal('create', info.startStr, info.endStr);
                calendar.unselect();
            },
            // Click day number → open create modal for that day
            navLinkDayClick: function (date) {
                if (!IS_EDITABLE) return;
                var str = date.toISOString().slice(0,10) + 'T08:00';
                openEventFormModal('create', str, str);
            },
            // Click event → view modal
            eventClick: function (info) {
                info.jsEvent.preventDefault();
                openEventViewModal(info.event);
            },
            // Drag-and-drop resize → save
            eventResize: function (info) {
                if (!IS_EDITABLE) return;
                fetch(SITEURL + '/calendar-crud-ajax', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({
                        type: 'edit',
                        id:    info.event.id,
                        title: info.event.title,
                        start: info.event.startStr,
                        end:   info.event.endStr,
                        color: info.event.backgroundColor
                    })
                }).then(function(){ showToast('Event updated', 'success'); });
            },
            // Drag-and-drop move → save
            eventDrop: function (info) {
                if (!IS_EDITABLE) return;
                fetch(SITEURL + '/calendar-crud-ajax', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({
                        type: 'edit',
                        id:    info.event.id,
                        title: info.event.title,
                        start: info.event.startStr,
                        end:   info.event.endStr,
                        color: info.event.backgroundColor
                    })
                }).then(function(){ showToast('Event rescheduled', 'success'); });
            },
            // Render event — in v6 list view uses standard rendering (no custom content needed)
            eventContent: function (arg) {
                if (arg.view.type === 'listWeek') {
                    return {
                        html: '<span class="fc-list-event-dot" style="background-color:' + (arg.event.backgroundColor || '#6366f1') + '"></span>'
                              + '<span class="fc-list-event-title">' + arg.event.title + '</span>'
                    };
                }
            }
        });

        calendar.render();
    });

    // ── Expose add-event button for parent page ──────────────────────────
    window.openCreateEventModal = function () {
        document.getElementById('efm-event-id').value = '';
        openEventFormModal('create', '', '');
    };

}());
</script>
