<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\ForumVote;
use App\Models\SchoolClass;
use App\Traits\SchoolSession;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    use SchoolSession;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $sessionId = $this->getSchoolCurrentSession();
        $classId   = $request->query('class_id');
        $courseId  = $request->query('course_id');
        $search    = $request->query('search');
        $status    = $request->query('status'); // solved, unsolved

        $query = ForumThread::with(['author', 'schoolClass', 'course', 'solutionReply'])
            ->withCount('replies')
            ->where(function($q) use ($sessionId) {
                $q->where('session_id', $sessionId)->orWhereNull('session_id');
            })
            ->orderByDesc('is_pinned')
            ->latest('id');

        if ($classId)  $query->where('class_id', $classId);
        if ($courseId) $query->where('course_id', $courseId);

        if ($status === 'solved') {
            $query->whereHas('replies', fn($q) => $q->where('is_solution', true));
        } elseif ($status === 'unsolved') {
            $query->whereDoesntHave('replies', fn($q) => $q->where('is_solution', true));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $threads = $query->paginate(15)->withQueryString();

        $classes = SchoolClass::where('session_id', $sessionId)->get();
        $courses = Course::all();

        $stats = [
            'total_threads' => ForumThread::count(),
            'total_replies' => ForumReply::count(),
            'solved_count'  => ForumReply::where('is_solution', true)->count(),
        ];

        return view('forum.index', compact('threads', 'classes', 'courses', 'stats', 'classId', 'courseId', 'search', 'status'));
    }

    public function create()
    {
        $sessionId = $this->getSchoolCurrentSession();
        $classes   = SchoolClass::where('session_id', $sessionId)->get();
        $courses   = Course::all();

        return view('forum.create', compact('classes', 'courses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'     => 'required|string|max:255',
            'content'   => 'required|string',
            'class_id'  => 'nullable|exists:school_classes,id',
            'course_id' => 'nullable|exists:courses,id',
        ]);

        $thread = ForumThread::create([
            'school_id'  => auth()->user()->school_id ?? 1,
            'user_id'    => auth()->id(),
            'class_id'   => $data['class_id'] ?? null,
            'course_id'  => $data['course_id'] ?? null,
            'title'      => $data['title'],
            'content'    => $data['content'],
            'session_id' => $this->getSchoolCurrentSession(),
        ]);

        return redirect()->route('forum.show', $thread->id)
            ->with('status', 'Question/discussion topic created successfully.');
    }

    public function show(int $id)
    {
        $thread = ForumThread::with(['author', 'schoolClass', 'course'])
            ->findOrFail($id);

        $thread->increment('views_count');

        $replies = ForumReply::with('author')
            ->where('thread_id', $id)
            ->orderByDesc('is_solution')
            ->orderByDesc('upvotes_count')
            ->oldest()
            ->get();

        return view('forum.show', compact('thread', 'replies'));
    }

    public function reply(Request $request, int $threadId)
    {
        $thread = ForumThread::findOrFail($threadId);

        abort_if($thread->is_locked, 403, 'This discussion is locked for replies.');

        $data = $request->validate([
            'content' => 'required|string|min:3',
        ]);

        ForumReply::create([
            'school_id' => auth()->user()->school_id ?? 1,
            'thread_id' => $thread->id,
            'user_id'   => auth()->id(),
            'content'   => $data['content'],
        ]);

        return back()->with('status', 'Your answer/reply has been posted.');
    }

    public function toggleVote(int $replyId)
    {
        $reply = ForumReply::findOrFail($replyId);
        $userId = auth()->id();

        $existing = ForumVote::where('user_id', $userId)
            ->where('reply_id', $replyId)
            ->first();

        if ($existing) {
            $existing->delete();
            $reply->decrement('upvotes_count');
            $voted = false;
        } else {
            ForumVote::create(['user_id' => $userId, 'reply_id' => $replyId]);
            $reply->increment('upvotes_count');
            $voted = true;
        }

        if (request()->wantsJson()) {
            return response()->json([
                'voted' => $voted,
                'count' => $reply->fresh()->upvotes_count,
            ]);
        }

        return back();
    }

    public function markSolution(int $replyId)
    {
        $reply  = ForumReply::with('thread')->findOrFail($replyId);
        $thread = $reply->thread;

        $user = auth()->user();
        abort_if(
            $thread->user_id !== $user->id && !$user->hasRole(['teacher', 'class-teacher', 'admin', 'principal']),
            403,
            'Only teachers or the question author can mark the verified solution.'
        );

        // Toggle solution
        $newStatus = !$reply->is_solution;
        if ($newStatus) {
            ForumReply::where('thread_id', $thread->id)->update(['is_solution' => false]);
        }
        $reply->update(['is_solution' => $newStatus]);

        return back()->with('status', $newStatus ? 'Answer marked as verified solution.' : 'Solution badge removed.');
    }

    public function togglePin(int $id)
    {
        $this->authorize('view staff'); // admin/teacher
        $thread = ForumThread::findOrFail($id);
        $thread->update(['is_pinned' => !$thread->is_pinned]);

        return back()->with('status', $thread->is_pinned ? 'Thread pinned to top.' : 'Thread unpinned.');
    }

    public function toggleLock(int $id)
    {
        $this->authorize('view staff'); // admin/teacher
        $thread = ForumThread::findOrFail($id);
        $thread->update(['is_locked' => !$thread->is_locked]);

        return back()->with('status', $thread->is_locked ? 'Thread locked.' : 'Thread unlocked.');
    }

    public function destroy(int $id)
    {
        $thread = ForumThread::findOrFail($id);
        abort_if(
            $thread->user_id !== auth()->id() && !auth()->user()->hasRole(['admin', 'principal']),
            403, 'Unauthorized'
        );

        $thread->delete();

        return redirect()->route('forum.index')->with('status', 'Discussion topic removed.');
    }
}
