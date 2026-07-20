<?php

namespace App\Http\Controllers;

use App\Models\OnlineClass;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\SchoolClassInterface;

class OnlineClassController extends Controller
{
    use SchoolSession;
    protected $schoolSessionRepository;
    protected $schoolClassRepository;

    public function __construct(
        SchoolSessionInterface $schoolSessionRepository,
        SchoolClassInterface   $schoolClassRepository
    ) {
        $this->middleware('auth');
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository   = $schoolClassRepository;
    }

    public function index(Request $request)
    {
        $session_id = $this->getSchoolCurrentSession();
        $user       = auth()->user();

        $query = OnlineClass::with(['course', 'schoolClass', 'section', 'teacher'])
            ->where('session_id', $session_id);

        if ($user->hasRole(['teacher', 'class-teacher'])) {
            $query->where('teacher_id', $user->id);
        } elseif ($user->hasRole('student')) {
            $promotion = \App\Models\Promotion::where('student_id', $user->id)
                ->where('session_id', $session_id)
                ->latest()->first();
            if ($promotion) {
                $query->where('class_id', $promotion->class_id)
                      ->where(fn($q) =>
                          $q->whereNull('section_id')
                            ->orWhere('section_id', $promotion->section_id)
                      );
            }
        }

        if ($request->status) $query->where('status', $request->status);

        $classes = $this->schoolClassRepository->getAllBySession($session_id);
        $onlineClasses = $query->orderByDesc('scheduled_at')->paginate(20)->withQueryString();

        return view('online-classes.index', compact('onlineClasses', 'classes', 'session_id'));
    }

    public function create()
    {
        $this->authorize('create online classes');

        $session_id = $this->getSchoolCurrentSession();
        $classes    = $this->schoolClassRepository->getAllBySession($session_id);

        return view('online-classes.create', compact('classes', 'session_id'));
    }

    public function store(Request $request)
    {
        $this->authorize('create online classes');

        $session_id = $this->getSchoolCurrentSession();

        $data = $request->validate([
            'title'            => 'required|string|max:200',
            'description'      => 'nullable|string',
            'platform'         => 'required|in:google_meet,zoom,teams,custom',
            'meeting_url'      => 'required|url|max:500',
            'meeting_id'       => 'nullable|string|max:100',
            'meeting_password' => 'nullable|string|max:100',
            'scheduled_at'     => 'required|date|after_or_equal:now',
            'duration_minutes' => 'required|integer|min:15|max:300',
            'course_id'        => 'required|integer',
            'class_id'         => 'required|integer',
            'section_id'       => 'nullable|integer',
        ]);

        OnlineClass::create(array_merge($data, [
            'teacher_id' => auth()->id(),
            'session_id' => $session_id,
            'status'     => 'scheduled',
        ]));

        return redirect()->route('online-classes.index')->with('status', 'Online class scheduled.');
    }

    public function edit(int $id)
    {
        $this->authorize('create online classes');

        $onlineClass = OnlineClass::findOrFail($id);

        abort_if($onlineClass->teacher_id !== auth()->id(), 403, 'You can only edit your own classes.');

        $session_id = $this->getSchoolCurrentSession();
        $classes    = $this->schoolClassRepository->getAllBySession($session_id);

        return view('online-classes.edit', compact('onlineClass', 'classes', 'session_id'));
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('create online classes');

        $onlineClass = OnlineClass::findOrFail($id);

        abort_if($onlineClass->teacher_id !== auth()->id(), 403);

        $data = $request->validate([
            'title'            => 'required|string|max:200',
            'description'      => 'nullable|string',
            'platform'         => 'required|in:google_meet,zoom,teams,custom',
            'meeting_url'      => 'required|url|max:500',
            'meeting_id'       => 'nullable|string|max:100',
            'meeting_password' => 'nullable|string|max:100',
            'scheduled_at'     => 'required|date',
            'duration_minutes' => 'required|integer|min:15|max:300',
            'status'           => 'required|in:scheduled,live,completed,cancelled',
            'recording_url'    => 'nullable|url|max:500',
        ]);

        $onlineClass->update($data);

        return back()->with('status', 'Online class updated.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create online classes');

        $onlineClass = OnlineClass::where('teacher_id', auth()->id())->findOrFail($id);
        $onlineClass->delete();

        return back()->with('status', 'Online class deleted.');
    }

    /** Update status quickly (e.g. go live, mark completed) */
    public function updateStatus(Request $request, int $id)
    {
        $this->authorize('create online classes');

        $onlineClass = OnlineClass::findOrFail($id);
        abort_if($onlineClass->teacher_id !== auth()->id(), 403);

        $request->validate(['status' => 'required|in:scheduled,live,completed,cancelled']);
        $onlineClass->update(['status' => $request->status]);

        return back()->with('status', 'Status updated.');
    }
}
