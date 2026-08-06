<?php
namespace App\Http\Controllers;

use App\Models\DisciplinaryRecord;
use App\Models\SchoolClass;
use App\Models\Promotion;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use Illuminate\Http\Request;

class DisciplinaryRecordController extends Controller
{
    use SchoolSession;
    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository)
    {
        $this->middleware(['can:view students']);
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    /**
     * GET /discipline — teacher-facing conduct log with class filter.
     */
    public function index(Request $request)
    {
        $sessionId = $this->getSchoolCurrentSession();
        $classId   = (int) $request->query('class_id', 0);

        $classes = SchoolClass::where('session_id', $sessionId)->orderBy('class_name')->get();

        $query = DisciplinaryRecord::with(['student', 'reporter'])
            ->orderByDesc('incident_date');

        if ($classId) {
            $studentIds = Promotion::where('session_id', $sessionId)
                ->where('class_id', $classId)
                ->pluck('student_id');
            $query->whereIn('student_id', $studentIds);
        } elseif (auth()->user()->hasRole(['teacher', 'class-teacher'])) {
            // Teachers see only students from their own classes
            $taughtClassIds = \App\Models\AssignedTeacher::where('teacher_id', auth()->id())
                ->distinct('class_id')->pluck('class_id');
            $studentIds = Promotion::where('session_id', $sessionId)
                ->whereIn('class_id', $taughtClassIds)
                ->pluck('student_id');
            $query->whereIn('student_id', $studentIds);
        }

        $records = $query->paginate(30)->withQueryString();

        return view('discipline.index', compact('records', 'classes', 'classId', 'sessionId'));
    }

    public function store(Request $request, int $studentId)
    {
        $this->authorize('create students');

        $data = $request->validate([
            'incident_date'   => 'required|date',
            'severity'        => 'required|in:minor,moderate,major',
            'incident_type'   => 'required|string|max:255',
            'description'     => 'required|string|max:3000',
            'action_taken'    => 'nullable|string|max:2000',
            'parent_notified' => 'nullable|string|max:1000',
        ]);

        DisciplinaryRecord::create(array_merge($data, [
            'student_id'  => $studentId,
            'reported_by' => auth()->id(),
            'resolved'    => false,
        ]));

        return back()->with('status', 'Disciplinary record added.');
    }

    public function resolve(int $id)
    {
        $this->authorize('create students');
        $record = DisciplinaryRecord::findOrFail($id);
        $record->update(['resolved' => !$record->resolved]);
        return back()->with('status', $record->resolved ? 'Marked as resolved.' : 'Marked as unresolved.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create students');
        DisciplinaryRecord::findOrFail($id)->delete();
        return back()->with('status', 'Record deleted.');
    }
}
