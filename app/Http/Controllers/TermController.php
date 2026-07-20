<?php

namespace App\Http\Controllers;

use App\Models\Term;
use App\Models\Semester;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\SemesterInterface;

class TermController extends Controller
{
    use SchoolSession;
    protected $schoolSessionRepository;
    protected $semesterRepository;

    public function __construct(
        SchoolSessionInterface $schoolSessionRepository,
        SemesterInterface $semesterRepository
    ) {
        $this->middleware('auth');
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->semesterRepository      = $semesterRepository;
    }

    public function index()
    {
        $this->authorize('view academic settings');

        $session_id = $this->getSchoolCurrentSession();
        $terms      = Term::with('semester')
            ->where('session_id', $session_id)
            ->orderBy('start_date')
            ->get();

        $semesters = $this->semesterRepository->getAll($session_id);

        return view('terms.index', compact('terms', 'semesters', 'session_id'));
    }

    public function store(Request $request)
    {
        $this->authorize('view academic settings');

        $session_id = $this->getSchoolCurrentSession();

        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'semester_id' => 'required|exists:semesters,id',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string|max:500',
        ]);

        Term::create(array_merge($data, ['session_id' => $session_id]));

        return back()->with('status', "Term '{$data['name']}' created.");
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('view academic settings');

        $term = Term::findOrFail($id);

        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'semester_id' => 'required|exists:semesters,id',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ]);

        $term->update(array_merge($data, ['is_active' => $request->boolean('is_active')]));

        return back()->with('status', 'Term updated.');
    }

    public function destroy(int $id)
    {
        $this->authorize('view academic settings');

        $term = Term::findOrFail($id);
        $term->delete();

        return back()->with('status', "Term '{$term->name}' deleted.");
    }
}
