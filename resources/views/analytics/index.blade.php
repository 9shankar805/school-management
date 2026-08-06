@extends('layouts.app')
@section('title', 'Central Analytics')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-bold text-gray-800 mb-1"><i class="bi bi-pie-chart text-indigo-500"></i> Central Analytics</h2>
            <p class="text-muted mb-0">Unified insights across Academics, Finance, HR, and Operations.</p>
        </div>
        <div>
            <button class="btn btn-outline-primary shadow-sm"><i class="bi bi-download"></i> Export Report</button>
        </div>
    </div>

    <!-- Top KPI Row -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="text-muted text-sm font-semibold mb-1">Total Enrollment</p>
                    <h3 class="font-bold text-gray-800 mb-0">{{ number_format($kpi->totalStudents) }}</h3>
                    <p class="text-{{ $kpi->enrollmentDeltaPct >= 0 ? 'success' : 'danger' }} text-xs mb-0 mt-2">
                        <i class="bi bi-arrow-{{ $kpi->enrollmentDeltaPct >= 0 ? 'up-right' : 'down-right' }}"></i>
                        {{ $kpi->enrollmentDeltaPct >= 0 ? '+' : '' }}{{ $kpi->enrollmentDeltaPct }}% from last year
                    </p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="text-muted text-sm font-semibold mb-1">Today's Attendance</p>
                    <h3 class="font-bold text-gray-800 mb-0">{{ $kpi->todayAttendancePct }}%</h3>
                    <p class="text-{{ $kpi->attendanceDeltaPct >= 0 ? 'success' : 'danger' }} text-xs mb-0 mt-2">
                        <i class="bi bi-arrow-{{ $kpi->attendanceDeltaPct >= 0 ? 'up-right' : 'down-right' }}"></i>
                        {{ $kpi->attendanceDeltaPct >= 0 ? '+' : '' }}{{ $kpi->attendanceDeltaPct }}% from yesterday
                    </p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="text-muted text-sm font-semibold mb-1">Fee Collection (YTD)</p>
                    <h3 class="font-bold text-gray-800 mb-0">Rs. {{ number_format($kpi->ytdFees) }}</h3>
                    <p class="text-{{ $kpi->feeDeltaPct >= 0 ? 'success' : 'danger' }} text-xs mb-0 mt-2">
                        <i class="bi bi-arrow-{{ $kpi->feeDeltaPct >= 0 ? 'up-right' : 'down-right' }}"></i>
                        {{ $kpi->feeDeltaPct >= 0 ? '+' : '' }}{{ $kpi->feeDeltaPct }}% vs target
                    </p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="text-muted text-sm font-semibold mb-1">Avg Performance Score</p>
                    <h3 class="font-bold text-gray-800 mb-0">{{ $kpi->performanceGrade }}</h3>
                    <p class="text-muted text-xs mb-0 mt-2">{{ $kpi->performanceDescription }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row g-4 mb-4">
        <!-- Revenue Forecast -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h6 class="font-bold text-gray-800 mb-0">Revenue Forecast (6 Mo Actual + 3 Mo Projected)</h6>
                </div>
                <div class="card-body p-4">
                    <div id="chart-revenue-forecast" style="height: 300px;"></div>
                </div>
            </div>
        </div>

        <!-- Department Stats -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h6 class="font-bold text-gray-800 mb-0">Staff Distribution</h6>
                </div>
                <div class="card-body p-4">
                    <div id="chart-department-stats" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row g-4 mb-4">
        <!-- Performance Heatmap -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h6 class="font-bold text-gray-800 mb-0">Student Performance Heatmap</h6>
                </div>
                <div class="card-body p-4">
                    <div id="chart-performance-heatmap" style="height: 350px;"></div>
                </div>
            </div>
        </div>

        <!-- Teacher Performance Radar -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h6 class="font-bold text-gray-800 mb-0">Teacher Performance Benchmarks</h6>
                </div>
                <div class="card-body p-4">
                    <div id="chart-teacher-radar" style="height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 3 -->
    <div class="row g-4 mb-4">
        <!-- Attendance Trend -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h6 class="font-bold text-gray-800 mb-0">Attendance Trend (7 Days)</h6>
                </div>
                <div class="card-body p-4">
                    <div id="chart-attendance-trend" style="height: 300px;"></div>
                </div>
            </div>
        </div>

        <!-- YOY Enrollment -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h6 class="font-bold text-gray-800 mb-0">Year-over-Year Enrollment</h6>
                </div>
                <div class="card-body p-4">
                    <div id="chart-yoy-enrollment" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dropout Risk Table -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="font-bold text-danger mb-0"><i class="bi bi-exclamation-triangle-fill"></i> Dropout Risk Watchlist</h6>
                    <button class="btn btn-sm btn-outline-danger">View Full List</button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted text-uppercase text-xs font-semibold">
                                <tr>
                                    <th class="ps-4">Student Name</th>
                                    <th>Grade</th>
                                    <th>Attendance</th>
                                    <th>Academic Flags</th>
                                    <th>Risk Level</th>
                                    <th class="pe-4 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dropoutRisk as $student)
                                <tr>
                                    <td class="ps-4 font-semibold text-gray-800">{{ $student['name'] }}</td>
                                    <td class="text-muted text-sm">{{ $student['grade'] }}</td>
                                    <td class="text-muted text-sm">{{ $student['attendance'] }}</td>
                                    <td class="text-muted text-sm">{{ $student['grades'] }}</td>
                                    <td>
                                        @if($student['risk'] === 'High')
                                            <span class="badge bg-danger rounded-pill px-3">{{ $student['risk'] }} Risk</span>
                                        @else
                                            <span class="badge bg-warning text-dark rounded-pill px-3">{{ $student['risk'] }} Risk</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-end">
                                        <button class="btn btn-sm btn-light">Schedule Meeting</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 1. Revenue Forecast Chart
        var revOptions = {
            series: [{
                name: 'Actual Revenue',
                data: @json($revenueData['actual'])
            }, {
                name: 'Forecasted Revenue',
                data: @json($revenueData['forecast'])
            }],
            chart: {
                type: 'area',
                height: 300,
                toolbar: { show: false }
            },
            colors: ['#4f46e5', '#9333ea'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2, dashArray: [0, 5] },
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] }
            },
            xaxis: { categories: @json($revenueData['categories']) },
            yaxis: {
                labels: { formatter: function (val) { return "$" + (val/1000).toFixed(0) + "k"; } }
            }
        };
        new ApexCharts(document.querySelector("#chart-revenue-forecast"), revOptions).render();

        // 2. Department Stats (Donut)
        var deptOptions = {
            series: @json($departmentStats['series']),
            labels: @json($departmentStats['labels']),
            chart: { type: 'donut', height: 300 },
            colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
            legend: { position: 'bottom' },
            dataLabels: { enabled: false }
        };
        new ApexCharts(document.querySelector("#chart-department-stats"), deptOptions).render();

        // 3. Performance Heatmap
        var heatmapOptions = {
            series: @json($heatmapData),
            chart: { type: 'heatmap', height: 350, toolbar: { show: false } },
            plotOptions: {
                heatmap: {
                    shadeIntensity: 0.5,
                    colorScale: {
                        ranges: [
                            { from: 0, to: 65, color: '#ef4444', name: 'Low' },
                            { from: 66, to: 80, color: '#f59e0b', name: 'Medium' },
                            { from: 81, to: 100, color: '#10b981', name: 'High' }
                        ]
                    }
                }
            },
            dataLabels: { enabled: true }
        };
        new ApexCharts(document.querySelector("#chart-performance-heatmap"), heatmapOptions).render();

        // 4. Teacher Performance Radar
        var radarOptions = {
            series: @json($teacherPerformance['series']),
            chart: { type: 'radar', height: 350, toolbar: { show: false } },
            labels: @json($teacherPerformance['categories']),
            stroke: { width: 2 },
            fill: { opacity: 0.2 },
            markers: { size: 4 },
            colors: ['#94a3b8', '#3b82f6']
        };
        new ApexCharts(document.querySelector("#chart-teacher-radar"), radarOptions).render();

        // 5. Attendance Trend
        var attOptions = {
            series: [{ name: 'Attendance %', data: @json($attendanceTrend['data']) }],
            chart: { type: 'line', height: 300, toolbar: { show: false } },
            stroke: { curve: 'smooth', width: 3 },
            colors: ['#10b981'],
            markers: { size: 5 },
            xaxis: { categories: @json($attendanceTrend['categories']) },
            yaxis: { min: 80, max: 100 }
        };
        new ApexCharts(document.querySelector("#chart-attendance-trend"), attOptions).render();

        // 6. YOY Enrollment
        var yoyOptions = {
            series: [{ name: 'Students', data: @json($enrollmentData['data']) }],
            chart: { type: 'bar', height: 300, toolbar: { show: false } },
            plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
            colors: ['#6366f1'],
            xaxis: { categories: @json($enrollmentData['categories']) }
        };
        new ApexCharts(document.querySelector("#chart-yoy-enrollment"), yoyOptions).render();
    });
</script>
@endpush
