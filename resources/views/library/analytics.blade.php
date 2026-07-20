@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-bar-chart-line"></i> Library Analytics</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('library.index') }}">Library</a></li>
                            <li class="breadcrumb-item active">Analytics</li>
                        </ol>
                    </nav>

                    {{-- KPI row --}}
                    <div class="row g-3 mb-4">
                        @php
                            $kpis = [
                                ['val' => $totalBooks,   'label' => 'Book Titles',      'icon' => 'bi-journals',          'color' => 'primary'],
                                ['val' => $totalCopies,  'label' => 'Total Copies',      'icon' => 'bi-stack',             'color' => 'info'],
                                ['val' => $available,    'label' => 'Available',          'icon' => 'bi-check2-circle',     'color' => 'success'],
                                ['val' => $issued,       'label' => 'On Loan',            'icon' => 'bi-box-arrow-up-right','color' => 'warning'],
                                ['val' => $overdue,      'label' => 'Overdue',            'icon' => 'bi-clock-history',     'color' => 'danger'],
                                ['val' => $totalMembers, 'label' => 'Members',            'icon' => 'bi-people',            'color' => 'secondary'],
                                ['val' => $totalEbooks,  'label' => 'E-Books',            'icon' => 'bi-file-earmark-text', 'color' => 'purple'],
                                ['val' => '$' . number_format($pendingFines, 2), 'label' => 'Pending Fines', 'icon' => 'bi-cash-coin', 'color' => 'danger'],
                            ];
                        @endphp
                        @foreach($kpis as $kpi)
                        <div class="col-6 col-md-3">
                            <div class="card border-0 bg-{{ $kpi['color'] === 'purple' ? 'secondary' : $kpi['color'] }} bg-opacity-10 text-center py-3">
                                <div class="mb-1"><i class="bi {{ $kpi['icon'] }} fs-4 text-{{ $kpi['color'] === 'purple' ? 'secondary' : $kpi['color'] }}"></i></div>
                                <div class="fs-4 fw-bold text-{{ $kpi['color'] === 'purple' ? 'secondary' : $kpi['color'] }}">{{ $kpi['val'] }}</div>
                                <div class="small text-muted">{{ $kpi['label'] }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="row g-4 mb-4">
                        {{-- Monthly issues chart --}}
                        <div class="col-md-8">
                            <div class="card shadow-sm">
                                <div class="card-header fw-semibold">Monthly Book Issues (Last 12 Months)</div>
                                <div class="card-body">
                                    <div id="monthlyIssuesChart"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Category distribution --}}
                        <div class="col-md-4">
                            <div class="card shadow-sm">
                                <div class="card-header fw-semibold">Books by Category</div>
                                <div class="card-body">
                                    <div id="categoryChart"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        {{-- Most issued books --}}
                        <div class="col-md-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-header fw-semibold"><i class="bi bi-trophy"></i> Most Issued Books</div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light"><tr><th>#</th><th>Title</th><th class="text-center">Times Issued</th></tr></thead>
                                        <tbody>
                                            @forelse($mostIssued as $i => $book)
                                            <tr>
                                                <td class="text-muted">{{ $i + 1 }}</td>
                                                <td>
                                                    <a href="{{ route('library.show', $book->id) }}" class="text-decoration-none">
                                                        {{ Str::limit($book->title, 35) }}
                                                    </a>
                                                    <div class="text-muted" style="font-size:.75rem">{{ $book->author }}</div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary">{{ $book->issues_count }}</span>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="3" class="text-center text-muted py-3">No data.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Overdue list --}}
                        <div class="col-md-6">
                            <div class="card shadow-sm h-100 border-danger">
                                <div class="card-header fw-semibold text-danger"><i class="bi bi-exclamation-triangle"></i> Overdue Books</div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light"><tr><th>Book</th><th>Member</th><th>Due</th><th>Days</th></tr></thead>
                                        <tbody>
                                            @forelse($overdueList as $issue)
                                            <tr>
                                                <td class="small">{{ Str::limit($issue->book->title, 25) }}</td>
                                                <td class="small">{{ $issue->member->user->first_name }} {{ $issue->member->user->last_name }}</td>
                                                <td class="small text-danger">{{ $issue->due_date->format('d M Y') }}</td>
                                                <td><span class="badge bg-danger">{{ $issue->calculated_overdue_days }}</span></td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="4" class="text-center text-muted py-3">No overdue books.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Members with outstanding fines --}}
                    @if($membersWithFines->isNotEmpty())
                    <div class="card shadow-sm mb-4">
                        <div class="card-header fw-semibold text-warning"><i class="bi bi-cash-coin"></i> Members with Outstanding Fines</div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light"><tr><th>Member</th><th>Card</th><th>Type</th><th>Outstanding Fine</th><th>Action</th></tr></thead>
                                <tbody>
                                    @foreach($membersWithFines as $member)
                                    <tr>
                                        <td>{{ $member->user->first_name }} {{ $member->user->last_name }}</td>
                                        <td><code>{{ $member->card_number }}</code></td>
                                        <td><span class="badge bg-secondary">{{ ucfirst($member->member_type) }}</span></td>
                                        <td><span class="text-danger fw-bold">${{ number_format($member->outstanding_fine, 2) }}</span></td>
                                        <td><a href="{{ route('library.members.show', $member->id) }}" class="btn btn-sm btn-outline-warning">View</a></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Monthly Issues Chart
const monthlyData = @json($monthlyIssues);
const months = Object.keys(monthlyData);
const counts = Object.values(monthlyData);

new ApexCharts(document.getElementById('monthlyIssuesChart'), {
    series: [{ name: 'Issues', data: counts }],
    chart: { type: 'area', height: 220, toolbar: { show: false } },
    xaxis: { categories: months },
    colors: ['#0d6efd'],
    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.1 } },
    stroke: { curve: 'smooth', width: 2 },
    dataLabels: { enabled: false },
    tooltip: { x: { format: 'yyyy-MM' } },
}).render();

// Category Donut Chart
const catLabels = @json($categoryStats->pluck('name'));
const catCounts = @json($categoryStats->pluck('books_count'));

new ApexCharts(document.getElementById('categoryChart'), {
    series: catCounts,
    labels: catLabels,
    chart: { type: 'donut', height: 220 },
    legend: { position: 'bottom', fontSize: '11px' },
    dataLabels: { enabled: true, formatter: (val) => Math.round(val) + '%' },
}).render();
</script>
@endpush
