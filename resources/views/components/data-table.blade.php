{{--
  Data Table component
  Usage:
    <x-data-table
        id="students-table"
        :columns="[
            ['key' => 'photo',     'label' => '',            'sortable' => false, 'width' => '3rem'],
            ['key' => 'name',      'label' => 'Name',        'sortable' => true],
            ['key' => 'email',     'label' => 'Email',       'sortable' => true],
            ['key' => 'class',     'label' => 'Class',       'sortable' => true],
            ['key' => 'status',    'label' => 'Status',      'sortable' => false],
            ['key' => 'actions',   'label' => 'Actions',     'sortable' => false, 'align' => 'right'],
        ]"
        :searchable="true"
        search-placeholder="Search students…"
        :per-page-options="[10, 25, 50, 100]"
        empty-icon="bi-people"
        empty-message="No students found."
    >
        @slot('toolbar')   ... extra toolbar buttons @endslot
        @slot('rows')
            <tr>...</tr>
        @endslot
        @slot('pagination') {{ $records->links('components.pagination') }} @endslot
    </x-data-table>
--}}
@props([
    'id'               => 'data-table-' . uniqid(),
    'columns'          => [],
    'searchable'       => true,
    'searchPlaceholder'=> 'Search…',
    'perPageOptions'   => [10, 25, 50],
    'emptyIcon'        => 'bi-inbox',
    'emptyMessage'     => 'No records found.',
    'cardView'         => false,   // enable card/table toggle
    'stickyHeader'     => true,
    'striped'          => false,
])

<div class="table-wrapper" id="{{ $id }}-wrapper">

    {{-- ── Toolbar ───────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-2 px-4 py-3 border-b" style="border-color:var(--border-card);background:var(--bg-surface)">

        {{-- Search --}}
        @if($searchable)
        <div class="relative flex-1 min-w-48">
            <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:var(--text-muted)"></i>
            <input type="text"
                   id="{{ $id }}-search"
                   data-table-search="{{ $id }}"
                   placeholder="{{ $searchPlaceholder }}"
                   class="input-modern pl-8 py-1.5 text-xs w-full"
                   autocomplete="off">
        </div>
        @endif

        {{-- Slot: extra toolbar items (filters, export buttons, etc.) --}}
        @if($slot->isNotEmpty() || isset($toolbar))
        <div class="flex items-center gap-2 flex-wrap">
            {{ $toolbar ?? '' }}
        </div>
        @endif

        {{-- Card / Table toggle --}}
        @if($cardView)
        <div class="flex items-center gap-1 border rounded-lg p-0.5" style="border-color:var(--border-card)" data-view-group>
            <button data-view-toggle="table" data-view-target="{{ $id }}-body"
                    class="px-2 py-1 rounded-md text-xs font-medium transition active"
                    style="color:var(--text-muted)"
                    title="Table view" data-tooltip="Table view">
                <i class="bi bi-table"></i>
            </button>
            <button data-view-toggle="grid" data-view-target="{{ $id }}-body"
                    class="px-2 py-1 rounded-md text-xs font-medium transition"
                    style="color:var(--text-muted)"
                    title="Card view" data-tooltip="Card view">
                <i class="bi bi-grid-3x3-gap"></i>
            </button>
        </div>
        @endif

    </div>

    {{-- ── Table ────────────────────────────────────────────── --}}
    <div class="table-scroll" id="{{ $id }}-body">
        <table class="table-modern {{ $striped ? 'table-striped' : '' }}" id="{{ $id }}">
            <thead>
                <tr>
                    @foreach($columns as $col)
                    <th class="{{ ($col['sortable'] ?? false) ? 'sortable' : '' }}"
                        @if(isset($col['width'])) style="width:{{ $col['width'] }}" @endif
                        align="{{ $col['align'] ?? 'left' }}">
                        {{ $col['label'] }}
                        @if($col['sortable'] ?? false)
                            <span class="sort-icon" aria-hidden="true"></span>
                        @endif
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                {{-- Rows slot --}}
                {{ $rows ?? '' }}

                {{-- Empty state (shown when no rows) --}}
                @if(empty($rows) || (isset($rows) && trim($rows) === ''))
                <tr class="empty-state-row">
                    <td colspan="{{ count($columns) }}" class="py-16 text-center" style="color:var(--text-muted)">
                        <i class="bi {{ $emptyIcon }} d-block mb-2" style="font-size:2rem;opacity:.3"></i>
                        <p class="text-sm">{{ $emptyMessage }}</p>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- ── Pagination slot ─────────────────────────────────── --}}
    @isset($pagination)
    <div class="px-4 py-3 border-t flex items-center justify-between gap-4 flex-wrap"
         style="border-color:var(--border-card);background:var(--bg-surface)">
        {{ $pagination }}
    </div>
    @endisset

</div>
