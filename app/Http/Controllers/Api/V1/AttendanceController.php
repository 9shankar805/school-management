<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $records = Attendance::query()
            ->when($request->date, fn($q) => $q->whereDate('date', $request->date))
            ->when($request->student_id, fn($q) => $q->where('student_id', $request->student_id))
            ->when($request->course_id, fn($q) => $q->where('course_id', $request->course_id))
            ->orderByDesc('date')
            ->paginate($request->integer('per_page', 50));

        return response()->json(['status' => 'success', 'data' => $records]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('take attendances');

        $request->validate([
            'records'               => ['required', 'array', 'min:1'],
            'records.*.student_id'  => ['required', 'integer', 'exists:users,id'],
            'records.*.course_id'   => ['required', 'integer', 'exists:courses,id'],
            'records.*.date'        => ['required', 'date'],
            'records.*.present'     => ['required', 'boolean'],
        ]);

        $saved = collect($request->records)->map(function ($record) {
            return Attendance::updateOrCreate(
                [
                    'student_id' => $record['student_id'],
                    'course_id'  => $record['course_id'],
                    'date'       => $record['date'],
                ],
                ['present' => $record['present']]
            );
        });

        return response()->json([
            'status'  => 'success',
            'message' => "Saved {$saved->count()} attendance records.",
        ]);
    }

    public function report(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => ['required', 'integer'],
            'from'       => ['nullable', 'date'],
            'to'         => ['nullable', 'date'],
        ]);

        $query = Attendance::where('student_id', $request->student_id);

        if ($request->from) $query->where('date', '>=', $request->from);
        if ($request->to)   $query->where('date', '<=', $request->to);

        $records = $query->get();
        $total   = $records->count();
        $present = $records->where('present', true)->count();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'total'      => $total,
                'present'    => $present,
                'absent'     => $total - $present,
                'percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
                'records'    => $records,
            ],
        ]);
    }
}
