<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * GET /api/v1/dashboard
     * Returns role-aware dashboard KPIs and chart data.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = match (true) {
            $user->hasRole('admin') => $this->adminDashboard(),
            $user->hasRole('teacher') => $this->teacherDashboard($user),
            $user->hasRole('student') => $this->studentDashboard($user),
            default => [],
        };

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    private function adminDashboard(): array
    {
        $today = now()->toDateString();

        return [
            'kpis' => [
                'total_students'  => User::role('student')->count(),
                'total_teachers'  => User::role('teacher')->count(),
                'total_classes'   => SchoolClass::count(),
                'today_present'   => Attendance::whereDate('date', $today)->where('present', true)->count(),
                'today_absent'    => Attendance::whereDate('date', $today)->where('present', false)->count(),
                'total_revenue'   => Payment::whereMonth('created_at', now()->month)->sum('amount'),
                'pending_invoices'=> Invoice::where('status', 'unpaid')->count(),
            ],
            'attendance_trend' => $this->getAttendanceTrend(),
            'revenue_trend'    => $this->getRevenueTrend(),
            'gender_distribution' => $this->getGenderDistribution(),
        ];
    }

    private function teacherDashboard(User $teacher): array
    {
        return [
            'my_courses'   => $teacher->assignedCourses()->with('section', 'course')->get() ?? [],
            'today_classes'=> [],  // TODO: join with routines
            'pending_marks'=> [],  // TODO: marks not yet submitted
        ];
    }

    private function studentDashboard(User $student): array
    {
        return [
            'attendance_percentage' => $this->getStudentAttendancePercentage($student->id),
            'upcoming_exams'        => [],  // TODO: join with exams + schedule
            'recent_marks'          => $student->marks()->latest()->take(5)->get(),
        ];
    }

    private function getAttendanceTrend(): array
    {
        return Attendance::select(
            DB::raw('DATE(date) as day'),
            DB::raw('SUM(present = 1) as present'),
            DB::raw('SUM(present = 0) as absent')
        )
            ->where('date', '>=', now()->subDays(30))
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->toArray();
    }

    private function getRevenueTrend(): array
    {
        return Payment::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(amount) as total')
        )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year')->orderBy('month')
            ->get()
            ->toArray();
    }

    private function getGenderDistribution(): array
    {
        return User::role('student')
            ->select('gender', DB::raw('count(*) as count'))
            ->groupBy('gender')
            ->pluck('count', 'gender')
            ->toArray();
    }

    private function getStudentAttendancePercentage(int $studentId): float
    {
        $total   = Attendance::where('student_id', $studentId)->count();
        $present = Attendance::where('student_id', $studentId)->where('present', true)->count();

        return $total > 0 ? round(($present / $total) * 100, 1) : 0;
    }
}
