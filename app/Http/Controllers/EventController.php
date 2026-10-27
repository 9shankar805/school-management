<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\NepaliDateService;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use Carbon\Carbon;

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
        $current_school_session_id = $this->getSchoolCurrentSession();

        if ($request->ajax()) {
            $query = Event::where('session_id', $current_school_session_id);

            if ($request->filled('start') && $request->filled('end')) {
                $query->whereDate('start', '>=', $request->start)
                      ->whereDate('end', '<=', $request->end);
            }

            if ($request->filled('category') && $request->category !== 'all') {
                $query->where('category', $request->category);
            }

            if ($request->filled('audience') && $request->audience !== 'all') {
                $query->where(function($q) use ($request) {
                    $q->where('audience', 'all')->orWhere('audience', $request->audience);
                });
            }

            if ($request->boolean('holidays_only')) {
                $query->where('is_holiday', true);
            }

            $events = $query->get();

            // Format for FullCalendar v6
            $formatted = $events->map(function ($event) {
                $catColors = Event::categoryColors();
                $color = $event->color ?: ($catColors[$event->category] ?? '#4f46e5');

                $startStr = $event->start ? Carbon::parse($event->start)->format('Y-m-d H:i:s') : null;
                $endStr   = $event->end ? Carbon::parse($event->end)->format('Y-m-d H:i:s') : $startStr;

                $startBs = $event->start_bs ?: ($event->start ? bsDate($event->start->format('Y-m-d'), true, 'short') : '');
                $endBs   = $event->end_bs ?: ($event->end ? bsDate($event->end->format('Y-m-d'), true, 'short') : '');

                return [
                    'id'              => $event->id,
                    'title'           => ($event->is_holiday ? '🎌 ' : '') . $event->title,
                    'start'           => $startStr,
                    'end'             => $endStr,
                    'backgroundColor' => $color,
                    'borderColor'     => $color,
                    'textColor'       => '#ffffff',
                    'extendedProps'   => [
                        'raw_title'     => $event->title,
                        'category'      => $event->category ?? 'academic',
                        'is_holiday'    => (bool) $event->is_holiday,
                        'start_bs'      => $startBs,
                        'end_bs'        => $endBs,
                        'audience'      => $event->audience ?? 'all',
                        'academic_term' => $event->academic_term,
                        'venue'         => $event->venue,
                        'description'   => $event->description,
                        'color'         => $color,
                    ],
                ];
            });

            return response()->json($formatted);
        }

        // Stats for the header cards
        $totalEvents     = Event::where('session_id', $current_school_session_id)->count();
        $totalHolidays   = Event::where('session_id', $current_school_session_id)->where('is_holiday', true)->count();
        $totalExams      = Event::where('session_id', $current_school_session_id)->where('category', 'exam')->count();
        
        $upcomingEvents  = Event::where('session_id', $current_school_session_id)
            ->where('start', '>=', now()->startOfDay())
            ->orderBy('start')
            ->take(8)
            ->get();

        $thisMonthEvents = Event::where('session_id', $current_school_session_id)
            ->whereMonth('start', now()->month)
            ->whereYear('start', now()->year)
            ->count();

        $categories = Event::categoryLabels();

        return view('events.index', compact(
            'totalEvents',
            'totalHolidays',
            'totalExams',
            'upcomingEvents',
            'thisMonthEvents',
            'categories'
        ));
    }

    /**
     * Create / Edit / Delete events via AJAX.
     */
    public function calendarEvents(Request $request)
    {
        $current_school_session_id = $this->getSchoolCurrentSession();
        $catColors = Event::categoryColors();

        switch ($request->type) {
            case 'create':
                $start = $request->start;
                $end   = $request->end ?? $request->start;
                $category = $request->category ?? 'academic';
                $isHoliday = $request->boolean('is_holiday') || ($category === 'holiday');
                $color = $request->color ?: ($catColors[$category] ?? '#4f46e5');

                $startBs = $request->start_bs;
                if (!$startBs && $start) {
                    $startBs = NepaliDateService::adToBS(Carbon::parse($start)->format('Y-m-d'));
                }

                $endBs = $request->end_bs;
                if (!$endBs && $end) {
                    $endBs = NepaliDateService::adToBS(Carbon::parse($end)->format('Y-m-d'));
                }

                $event = Event::create([
                    'title'         => $request->title,
                    'category'      => $category,
                    'is_holiday'    => $isHoliday,
                    'start'         => $start,
                    'end'           => $end,
                    'start_bs'      => $startBs,
                    'end_bs'        => $endBs,
                    'audience'      => $request->audience ?? 'all',
                    'academic_term' => $request->academic_term,
                    'venue'         => $request->venue,
                    'color'         => $color,
                    'description'   => $request->description,
                    'session_id'    => $current_school_session_id,
                ]);

                return response()->json([
                    'id'              => $event->id,
                    'title'           => ($event->is_holiday ? '🎌 ' : '') . $event->title,
                    'start'           => $event->start->toDateTimeString(),
                    'end'             => $event->end ? $event->end->toDateTimeString() : $event->start->toDateTimeString(),
                    'backgroundColor' => $event->color,
                    'borderColor'     => $event->color,
                    'textColor'       => '#ffffff',
                    'extendedProps'   => [
                        'raw_title'     => $event->title,
                        'category'      => $event->category,
                        'is_holiday'    => (bool) $event->is_holiday,
                        'start_bs'      => $event->start_bs,
                        'end_bs'        => $event->end_bs,
                        'audience'      => $event->audience,
                        'academic_term' => $event->academic_term,
                        'venue'         => $event->venue,
                        'description'   => $event->description,
                        'color'         => $event->color,
                    ],
                ]);

            case 'edit':
                $event = Event::find($request->id);
                if ($event) {
                    $category = $request->category ?? $event->category ?? 'academic';
                    $isHoliday = $request->has('is_holiday') ? $request->boolean('is_holiday') : $event->is_holiday;
                    $color = $request->color ?: ($catColors[$category] ?? $event->color);

                    $start = $request->start ?? $event->start;
                    $end   = $request->end ?? $event->end;

                    $startBs = $request->start_bs ?? $event->start_bs;
                    if (!$startBs && $start) {
                        $startBs = NepaliDateService::adToBS(Carbon::parse($start)->format('Y-m-d'));
                    }

                    $endBs = $request->end_bs ?? $event->end_bs;
                    if (!$endBs && $end) {
                        $endBs = NepaliDateService::adToBS(Carbon::parse($end)->format('Y-m-d'));
                    }

                    $event->update([
                        'title'         => $request->title ?? $event->title,
                        'category'      => $category,
                        'is_holiday'    => $isHoliday,
                        'start'         => $start,
                        'end'           => $end,
                        'start_bs'      => $startBs,
                        'end_bs'        => $endBs,
                        'audience'      => $request->audience ?? $event->audience,
                        'academic_term' => $request->academic_term ?? $event->academic_term,
                        'venue'         => $request->venue ?? $event->venue,
                        'color'         => $color,
                        'description'   => $request->description ?? $event->description,
                    ]);
                }
                return response()->json(['status' => 'updated', 'event' => $event]);

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

    /**
     * Generate standard Nepal School Academic Calendar & Holidays Preset for the current session.
     */
    public function generatePresets(Request $request)
    {
        $current_school_session_id = $this->getSchoolCurrentSession();
        $catColors = Event::categoryColors();

        $year = now()->year;
        $bsYear = NepaliDateService::bsYear(now()->toDateString()) ?? 2083;

        // Standard academic calendar events & national holidays for Nepal school system
        $presets = [
            // Academic Kickoff
            [
                'title' => 'नयाँ शैक्षिक सत्र शुभारम्भ तथा अभिमुखीकरण (Session Orientation & Admission)',
                'category' => 'academic',
                'is_holiday' => false,
                'start' => "{$year}-04-14 09:00:00",
                'end' => "{$year}-04-14 16:00:00",
                'academic_term' => 'First Term',
                'description' => 'नयाँ भर्ना तथा कक्षा सञ्चालन प्रारम्भ',
            ],
            [
                'title' => 'नयाँ वर्ष (Nepali New Year - Baisakh 1)',
                'category' => 'holiday',
                'is_holiday' => true,
                'start' => "{$year}-04-14 00:00:00",
                'end' => "{$year}-04-14 23:59:59",
                'academic_term' => 'First Term',
                'description' => 'नयाँ वर्षको सार्वजनिक बिदा',
            ],
            [
                'title' => 'बुद्ध जयन्ती तथा उभौली पर्व (Buddha Jayanti)',
                'category' => 'holiday',
                'is_holiday' => true,
                'start' => "{$year}-05-12 00:00:00",
                'end' => "{$year}-05-12 23:59:59",
                'academic_term' => 'First Term',
                'description' => 'बुद्ध जयन्ती सार्वजनिक बिदा',
            ],
            [
                'title' => 'प्रथम त्रैमासिक परीक्षा (First Terminal Examination)',
                'category' => 'exam',
                'is_holiday' => false,
                'start' => "{$year}-07-15 10:00:00",
                'end' => "{$year}-07-22 14:00:00",
                'academic_term' => 'First Term',
                'description' => 'कक्षा १ देखि १० सम्मको प्रथम त्रैमासिक परीक्षा',
            ],
            [
                'title' => 'प्रथम त्रैमासिक नतिजा तथा अभिभावक भेला (1st Term Result & PTM)',
                'category' => 'meeting',
                'is_holiday' => false,
                'start' => "{$year}-07-30 11:00:00",
                'end' => "{$year}-07-30 15:00:00",
                'academic_term' => 'First Term',
                'description' => 'अभिभावक-शिक्षक बैठक तथा नतिजा वितरण',
            ],
            [
                'title' => 'जनै पूर्णिमा तथा रक्षा बन्धन (Janai Purnima)',
                'category' => 'holiday',
                'is_holiday' => true,
                'start' => "{$year}-08-19 00:00:00",
                'end' => "{$year}-08-19 23:59:59",
                'academic_term' => 'Second Term',
                'description' => 'जनै पूर्णिमा बिदा',
            ],
            [
                'title' => 'श्रीकृष्ण जन्माष्टमी (Shree Krishna Janmashtami)',
                'category' => 'holiday',
                'is_holiday' => true,
                'start' => "{$year}-08-26 00:00:00",
                'end' => "{$year}-08-26 23:59:59",
                'academic_term' => 'Second Term',
                'description' => 'श्रीकृष्ण जन्माष्टमी बिदा',
            ],
            [
                'title' => 'संविधान दिवस (National Constitution Day - Asoj 3)',
                'category' => 'holiday',
                'is_holiday' => true,
                'start' => "{$year}-09-19 00:00:00",
                'end' => "{$year}-09-19 23:59:59",
                'academic_term' => 'Second Term',
                'description' => 'राष्ट्रिय संविधान दिवस बिदा',
            ],
            [
                'title' => 'बडा दशैं बिदा (Dashain Festival Vacation)',
                'category' => 'vacation',
                'is_holiday' => true,
                'start' => "{$year}-10-03 00:00:00",
                'end' => "{$year}-10-18 23:59:59",
                'academic_term' => 'Second Term',
                'description' => 'घटस्थापना देखि कोजाग्रत पूर्णिमा सम्म दशैं बिदा',
            ],
            [
                'title' => 'शुभ दीपावली, तिहार तथा छठ पर्व बिदा (Tihar & Chhath Vacation)',
                'category' => 'vacation',
                'is_holiday' => true,
                'start' => "{$year}-10-31 00:00:00",
                'end' => "{$year}-11-08 23:59:59",
                'academic_term' => 'Second Term',
                'description' => 'लक्ष्मी पूजा, गोवर्धन पूजा, भाइटीका तथा छठ पर्व बिदा',
            ],
            [
                'title' => 'वार्षिक खेलकुद तथा अतिरिक्त क्रियाकलाप सप्ताह (Annual Sports & ECA Week)',
                'category' => 'sports',
                'is_holiday' => false,
                'start' => "{$year}-11-20 09:00:00",
                'end' => "{$year}-11-25 16:00:00",
                'academic_term' => 'Second Term',
                'description' => 'अन्तर-सदन खेलकुद, दौड, रिले, बुद्धिचाल, हाजिरीजवाफ तथा वक्तृत्वकला',
            ],
            [
                'title' => 'दोस्रो त्रैमासिक परीक्षा (Mid-Term / 2nd Terminal Examination)',
                'category' => 'exam',
                'is_holiday' => false,
                'start' => "{$year}-12-15 10:00:00",
                'end' => "{$year}-12-23 14:00:00",
                'academic_term' => 'Second Term',
                'description' => 'दोस्रो त्रैमासिक अर्धवार्षिक परीक्षा',
            ],
            [
                'title' => 'हिउँदे बिदा (Winter Vacation)',
                'category' => 'vacation',
                'is_holiday' => true,
                'start' => "{$year}-12-28 00:00:00",
                'end' => ($year + 1) . "-01-08 23:59:59",
                'academic_term' => 'Final Term',
                'description' => 'अत्यधिक चिसोका कारण हिउँदे बिदा',
            ],
            [
                'title' => 'माघे सङ्क्रान्ति (Maghe Sankranti)',
                'category' => 'holiday',
                'is_holiday' => true,
                'start' => ($year + 1) . "-01-14 00:00:00",
                'end' => ($year + 1) . "-01-14 23:59:59",
                'academic_term' => 'Final Term',
                'description' => 'माघे सङ्क्रान्ति / माघी पर्व बिदा',
            ],
            [
                'title' => 'सरस्वती पूजा / श्रीपञ्चमी (Saraswati Puja - Vidhyarambha)',
                'category' => 'celebration',
                'is_holiday' => false,
                'start' => ($year + 1) . "-02-02 08:30:00",
                'end' => ($year + 1) . "-02-02 14:00:00",
                'academic_term' => 'Final Term',
                'description' => 'सरस्वती वन्दना, अक्षरारम्भ तथा सांस्कृतिक कार्यक्रम',
            ],
            [
                'title' => 'महाशिवरात्रि तथा राष्ट्रिय प्रजातन्त्र दिवस (Maha Shivaratri & Democracy Day)',
                'category' => 'holiday',
                'is_holiday' => true,
                'start' => ($year + 1) . "-02-19 00:00:00",
                'end' => ($year + 1) . "-02-19 23:59:59",
                'academic_term' => 'Final Term',
                'description' => 'शिवरात्रि तथा प्रजातन्त्र दिवस बिदा',
            ],
            [
                'title' => 'होली पर्व (Fagu Purnima / Holi)',
                'category' => 'holiday',
                'is_holiday' => true,
                'start' => ($year + 1) . "-03-14 00:00:00",
                'end' => ($year + 1) . "-03-14 23:59:59",
                'academic_term' => 'Final Term',
                'description' => 'होली पर्व रङ्गोत्सव बिदा',
            ],
            [
                'title' => 'वार्षिक अन्तिम परीक्षा (Annual Final Examination)',
                'category' => 'exam',
                'is_holiday' => false,
                'start' => ($year + 1) . "-03-18 10:00:00",
                'end' => ($year + 1) . "-03-28 14:00:00",
                'academic_term' => 'Final Term',
                'description' => 'वार्षिक सत्रान्त परीक्षा',
            ],
            [
                'title' => 'वार्षिक नतिजा प्रकाशन तथा पुरस्कार वितरण (Annual Result Day & Prize Distribution)',
                'category' => 'celebration',
                'is_holiday' => false,
                'start' => ($year + 1) . "-04-05 10:00:00",
                'end' => ($year + 1) . "-04-05 16:00:00",
                'academic_term' => 'Final Term',
                'description' => 'अन्तिम नतिजा प्रकाशन, लब्धांक पत्र वितरण तथा उत्कृष्ट विद्यार्थी पुरस्कार वितरण',
            ],
        ];

        $count = 0;
        foreach ($presets as $p) {
            $startBs = NepaliDateService::adToBS(Carbon::parse($p['start'])->format('Y-m-d'));
            $endBs   = NepaliDateService::adToBS(Carbon::parse($p['end'])->format('Y-m-d'));

            Event::firstOrCreate(
                [
                    'session_id' => $current_school_session_id,
                    'title'      => $p['title'],
                ],
                [
                    'category'      => $p['category'],
                    'is_holiday'    => $p['is_holiday'],
                    'start'         => $p['start'],
                    'end'           => $p['end'],
                    'start_bs'      => $startBs,
                    'end_bs'        => $endBs,
                    'academic_term' => $p['academic_term'],
                    'audience'      => 'all',
                    'color'         => $catColors[$p['category']] ?? '#4f46e5',
                    'description'   => $p['description'],
                ]
            );
            $count++;
        }

        return redirect()->route('events.show')->with('status', "{$count} standard academic calendar milestones and holidays generated successfully!");
    }

    /**
     * Print / Export beautiful Wall Academic Calendar.
     */
    public function printCalendar(Request $request)
    {
        $current_school_session_id = $this->getSchoolCurrentSession();

        $events = Event::where('session_id', $current_school_session_id)
            ->orderBy('start')
            ->get();

        // Group by month
        $groupedByMonth = $events->groupBy(function ($event) {
            return Carbon::parse($event->start)->format('F Y');
        });

        $totalHolidays = $events->where('is_holiday', true)->count();
        $totalExams    = $events->where('category', 'exam')->count();

        return view('events.print', compact('events', 'groupedByMonth', 'totalHolidays', 'totalExams'));
    }
}
