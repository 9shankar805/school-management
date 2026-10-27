<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Repositories\NoticeRepository;
use App\Http\Requests\NoticeStoreRequest;
use App\Interfaces\SchoolSessionInterface;

class NoticeController extends Controller
{
    use SchoolSession;
    
    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository) {
        $this->schoolSessionRepository = $schoolSessionRepository;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $current_school_session_id = $this->getSchoolCurrentSession();
        $audience = $request->input('audience');
        $search   = $request->input('search');

        $noticeRepository = new NoticeRepository();
        $notices = $noticeRepository->getAll($current_school_session_id, $audience, $search);

        $stats = [
            'total'    => Notice::where('session_id', $current_school_session_id)->count(),
            'urgent'   => Notice::where('session_id', $current_school_session_id)->where('priority', 'urgent')->count(),
            'holidays' => Notice::where('session_id', $current_school_session_id)->where('priority', 'holiday')->count(),
            'exams'    => Notice::where('session_id', $current_school_session_id)->where('priority', 'exam')->count(),
        ];

        return view('notices.index', compact('notices', 'stats', 'audience', 'search', 'current_school_session_id'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $current_school_session_id = $this->getSchoolCurrentSession();
        return view('notices.create', compact('current_school_session_id'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  NoticeStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(NoticeStoreRequest $request)
    {
        try {
            $noticeRepository = new NoticeRepository();
            $noticeRepository->store($request->validated());

            return redirect()->route('notice.index')->with('status', 'Notice published successfully!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Notice  $notice
     * @return \Illuminate\Http\Response
     */
    public function show(Notice $notice)
    {
        $school = auth()->user()->school ?? \App\Models\School::first();
        return view('notices.show', compact('notice', 'school'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Notice  $notice
     * @return \Illuminate\Http\Response
     */
    public function edit(Notice $notice)
    {
        $current_school_session_id = $this->getSchoolCurrentSession();
        return view('notices.edit', compact('notice', 'current_school_session_id'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Notice  $notice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Notice $notice)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'priority'    => 'nullable|string|in:normal,urgent,holiday,exam',
            'audience'    => 'nullable|string|in:all,parents,students,teachers',
            'expiry_date' => 'nullable|date',
            'notice'      => 'required|string',
            'attachment'  => 'nullable|file|mimes:pdf,jpg,jpeg,png,docx|max:10240',
        ]);

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('notices', 'public');
        }

        $notice->update($data);

        return redirect()->route('notice.index')->with('status', 'Notice updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Notice  $notice
     * @return \Illuminate\Http\Response
     */
    public function destroy(Notice $notice)
    {
        $notice->delete();
        return redirect()->route('notice.index')->with('status', 'Notice removed successfully.');
    }
}
