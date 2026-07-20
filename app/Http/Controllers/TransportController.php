<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\TransportRoute;
use App\Models\StudentTransport;
use App\Models\TransportAttendance;
use App\Models\FuelLog;
use App\Models\MaintenanceLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransportReportExport;

class TransportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage transport');
    }

    // ── Dashboard / Overview ──────────────────────────────────────────────────

    public function index()
    {
        $totalVehicles   = Vehicle::count();
        $activeVehicles  = Vehicle::active()->count();
        $inMaintenance   = Vehicle::where('status', 'maintenance')->count();
        $totalDrivers    = Driver::count();
        $activeDrivers   = Driver::active()->count();
        $totalRoutes     = TransportRoute::count();
        $activeRoutes    = TransportRoute::active()->count();
        $totalStudents   = StudentTransport::active()->count();

        // Expiry alerts
        $vehicleAlerts = Vehicle::where(function ($q) {
            $q->where('insurance_expiry', '<=', now()->addDays(30))
              ->orWhere('fitness_expiry', '<=', now()->addDays(30))
              ->orWhere('permit_expiry', '<=', now()->addDays(30));
        })->where('status', 'active')->get();

        $driverAlerts = Driver::where('license_expiry', '<=', now()->addDays(30))
                              ->where('status', 'active')->get();

        // Upcoming maintenance
        $upcomingMaint = MaintenanceLog::with('vehicle')
                                       ->upcoming()
                                       ->orderBy('service_date')
                                       ->take(5)->get();

        // Monthly fuel cost (last 6 months)
        $fuelByMonth = FuelLog::selectRaw("DATE_FORMAT(date, '%Y-%m') as month, SUM(total_cost) as cost")
            ->where('date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('cost', 'month');

        // Routes with student counts
        $routeStats = TransportRoute::active()
            ->withCount('activeStudents')
            ->with('vehicle')
            ->orderBy('name')
            ->get();

        // Today's attendance summary
        $todayPresent = TransportAttendance::where('date', today())->where('status', 'present')->count();
        $todayAbsent  = TransportAttendance::where('date', today())->where('status', 'absent')->count();

        return view('transport.dashboard', compact(
            'totalVehicles', 'activeVehicles', 'inMaintenance',
            'totalDrivers', 'activeDrivers',
            'totalRoutes', 'activeRoutes', 'totalStudents',
            'vehicleAlerts', 'driverAlerts', 'upcomingMaint',
            'fuelByMonth', 'routeStats',
            'todayPresent', 'todayAbsent'
        ));
    }

    // ── Analytics ─────────────────────────────────────────────────────────────

    public function analytics()
    {
        // Fleet breakdown
        $byType   = Vehicle::selectRaw('type, COUNT(*) as cnt')->groupBy('type')->pluck('cnt', 'type');
        $byStatus = Vehicle::selectRaw('status, COUNT(*) as cnt')->groupBy('status')->pluck('cnt', 'status');

        // Fuel — monthly litres & cost (12 months)
        $fuelMonthly = FuelLog::selectRaw(
            "DATE_FORMAT(date,'%Y-%m') as month, SUM(litres) as litres, SUM(total_cost) as cost"
        )->where('date', '>=', now()->subMonths(12))
         ->groupBy('month')->orderBy('month')
         ->get();

        // Maintenance cost by type
        $maintByType = MaintenanceLog::selectRaw('type, SUM(cost) as total')
                        ->where('status', 'completed')
                        ->groupBy('type')
                        ->pluck('total', 'type');

        // Attendance rate by route (last 30 days)
        $attByRoute = TransportRoute::active()
            ->withCount([
                'attendanceRecords as present_count' => fn($q) =>
                    $q->where('status', 'present')->where('date', '>=', now()->subDays(30)),
                'attendanceRecords as total_count' => fn($q) =>
                    $q->where('date', '>=', now()->subDays(30)),
            ])
            ->orderBy('name')
            ->get();

        // Top fuel consumers
        $topFuelVehicles = Vehicle::withSum(['fuelLogs as total_fuel_cost' => fn($q) =>
                $q->where('date', '>=', now()->subMonths(3))], 'total_cost')
            ->orderByDesc('total_fuel_cost')
            ->take(8)->get();

        return view('transport.analytics', compact(
            'byType', 'byStatus', 'fuelMonthly', 'maintByType',
            'attByRoute', 'topFuelVehicles'
        ));
    }

    // ── Reports ───────────────────────────────────────────────────────────────

    public function reportForm()
    {
        $routes = TransportRoute::orderBy('name')->get();
        return view('transport.reports', compact('routes'));
    }

    public function reportExport(Request $request)
    {
        $data = $request->validate([
            'report_type' => 'required|in:fleet,drivers,routes,students,fuel,maintenance',
            'format'      => 'required|in:pdf,excel',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date|after_or_equal:date_from',
            'route_id'    => 'nullable|integer|exists:transport_routes,id',
        ]);

        $reportType = $data['report_type'];
        $format     = $data['format'];
        $dateFrom   = $data['date_from'] ?? null;
        $dateTo     = $data['date_to'] ?? null;
        $routeId    = $data['route_id'] ?? null;

        $records = match ($reportType) {
            'fleet'       => Vehicle::with('driver')->orderBy('name')->get(),
            'drivers'     => Driver::with('currentVehicle')->orderBy('name')->get(),
            'routes'      => TransportRoute::with(['vehicle', 'driver'])->withCount('activeStudents')->orderBy('name')->get(),
            'students'    => StudentTransport::with(['student', 'route', 'stop'])
                                ->when($routeId, fn($q) => $q->where('route_id', $routeId))
                                ->active()->get(),
            'fuel'        => FuelLog::with('vehicle')
                                ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
                                ->when($dateTo,   fn($q) => $q->where('date', '<=', $dateTo))
                                ->orderBy('date')->get(),
            'maintenance' => MaintenanceLog::with('vehicle')
                                ->when($dateFrom, fn($q) => $q->where('service_date', '>=', $dateFrom))
                                ->when($dateTo,   fn($q) => $q->where('service_date', '<=', $dateTo))
                                ->orderBy('service_date')->get(),
        };

        $title = ucfirst($reportType) . ' Report';

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('transport.reports-pdf', compact('records', 'reportType', 'title', 'dateFrom', 'dateTo'))
                      ->setPaper('a4', 'landscape');
            return $pdf->download("transport-{$reportType}-report.pdf");
        }

        return Excel::download(
            new TransportReportExport($records, $reportType),
            "transport-{$reportType}-report.xlsx"
        );
    }
}
