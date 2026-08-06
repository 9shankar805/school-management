<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;

class EventController extends Controller
{
    use SchoolSession;
    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository)
    {
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    /**
     * Show the calendar page OR return events as JSON (AJAX).
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $current_school_session_id = $this->getSchoolCurrentSession();

            $events = Event::whereDate('start', '>=', $request->start)
                ->whereDate('end', '<=', $request->end)
                ->where('session_id', $current_school_session_id)
                ->get(['id', 'title', 'start', 'end', 'color', 'description']);

            // Format for FullCalendar v5
            $formatted = $events->map(function ($event) {
                return [
                    'id'                => $event->id,
                    'title'             => $event->title,
                    'start'             => $event->start,
                    'end'               => $event->end,
                    'backgroundColor'   => $event->color ?? '#6366f1',
                    'borderColor'       => $event->color ?? '#6366f1',
                    'textColor'         => '#ffffff',
                    'extendedProps'     => [
                        'description' => $event->description,
                    ],
                ];
            });

            return response()->json($formatted);
        }

        $current_school_session_id = $this->getSchoolCurrentSession();

        // Stats for the header cards
        $totalEvents  = Event::where('session_id', $current_school_session_id)->count();
        $upcomingEvents = Event::where('session_id', $current_school_session_id)
            ->where('start', '>=', now())
            ->orderBy('start')
            ->take(5)
            ->get();
        $thisMonthEvents = Event::where('session_id', $current_school_session_id)
            ->whereMonth('start', now()->month)
            ->whereYear('start', now()->year)
            ->count();

        return view('events.index', compact('totalEvents', 'upcomingEvents', 'thisMonthEvents'));
    }

    /**
     * Create / Edit / Delete events via AJAX.
     */
    public function calendarEvents(Request $request)
    {
        $current_school_session_id = $this->getSchoolCurrentSession();
        $event = null;

        switch ($request->type) {
            case 'create':
                $event = Event::create([
                    'title'       => $request->title,
                    'start'       => $request->start,
                    'end'         => $request->end ?? $request->start,
                    'color'       => $request->color ?? '#6366f1',
                    'description' => $request->description,
                    'session_id'  => $current_school_session_id,
                ]);
                return response()->json([
                    'id'              => $event->id,
                    'title'           => $event->title,
                    'start'           => $event->start,
                    'end'             => $event->end,
                    'backgroundColor' => $event->color,
                    'borderColor'     => $event->color,
                    'textColor'       => '#ffffff',
                    'extendedProps'   => ['description' => $event->description],
                ]);

            case 'edit':
                $event = Event::find($request->id);
                if ($event) {
                    $event->update([
                        'title'       => $request->title,
                        'start'       => $request->start,
                        'end'         => $request->end ?? $request->start,
                        'color'       => $request->color ?? $event->color,
                        'description' => $request->description ?? $event->description,
                    ]);
                }
                return response()->json(['status' => 'updated']);

            case 'delete':
                $event = Event::find($request->id);
                if ($event) {
                    $event->delete();
                }
                return response()->json(['status' => 'deleted']);

            default:
                return response()->json(['status' => 'no action'], 400);
        }
    }
}
