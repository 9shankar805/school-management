<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolLandingController extends Controller
{
    /**
     * Helper to resolve school by slug.
     */
    private function resolveSchoolBySlug(string $slug): School
    {
        $cleanSlug = str_replace('-', '', strtolower($slug));
        return School::where(function ($query) use ($slug, $cleanSlug) {
                $query->where('slug', $slug)
                      ->orWhereRaw("REPLACE(slug, '-', '') = ?", [$cleanSlug]);
            })
            ->where('status', 'active')
            ->firstOrFail();
    }

    /**
     * Helper to resolve school by subdomain.
     */
    private function resolveSchoolBySubdomain(string $subdomain): School
    {
        $sub = strtolower($subdomain);
        $cleanSub = str_replace('-', '', $sub);

        return School::where(function ($query) use ($sub, $cleanSub) {
                $query->where('slug', $sub)
                      ->orWhereRaw("REPLACE(slug, '-', '') = ?", [$cleanSub]);
            })
            ->where('status', 'active')
            ->firstOrFail();
    }

    /**
     * Load common data passed to all school portal views.
     */
    private function getSchoolData(School $school): array
    {
        $teachers = $school->users()
            ->whereIn('role', ['teacher', 'principal', 'vice-principal', 'academic-coordinator'])
            ->with(['qualifications', 'assignedCourses.schoolClass', 'assignedCourses.course'])
            ->take(24)
            ->get();

        if ($teachers->isEmpty()) {
            $teachers = $school->users()
                ->where('role', 'teacher')
                ->with(['qualifications', 'assignedCourses.schoolClass', 'assignedCourses.course'])
                ->take(24)
                ->get();
        }

        $committeeMembers = $school->committeeMembers()->take(10)->get();
        $recentNotices = $school->notices()
            ->withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where(function ($q) {
                $q->whereNull('audience')
                  ->orWhere('audience', '!=', 'teachers');
            })
            ->latest()
            ->take(20)
            ->get();
        $events = \App\Models\Event::where('school_id', $school->id)->latest()->take(20)->get();
        $classes = \App\Models\SchoolClass::where('school_id', $school->id)->with(['sections', 'courses'])->take(20)->get();

        // Smart Multi-Page Link Generator (Robust host check avoiding localhost being mistaken for subdomain)
        $host = strtolower(request()->getHost());
        $cleanHost = preg_replace('/:\d+$/', '', $host);
        $isSubdomain = request()->routeIs('school.subdomain*')
            || (
                str_contains($cleanHost, '.')
                && !in_array($cleanHost, ['localhost', '127.0.0.1', 'nshlms.com', 'nepalschoolhub.com', 'techprocod.com.np'])
                && preg_match('/^([a-z0-9\-]+)\./i', $cleanHost)
            );

        $pageUrl = function(string $page = '') use ($school, $isSubdomain): string {
            $page = ltrim($page, '/');
            if ($isSubdomain) {
                return $page ? url('/' . $page) : url('/');
            }
            return $page ? url('/school/' . $school->slug . '/' . $page) : url('/school/' . $school->slug);
        };

        $activeNav = function(string $page = '') use ($isSubdomain): bool {
            $path = trim(request()->path(), '/');
            if (empty($page)) {
                return request()->routeIs('school.landing') || request()->routeIs('school.subdomain') || $path === '' || preg_match('#^school/[^/]+$#', $path);
            }
            $cleanPage = trim($page, '/');
            return str_contains('/' . $path, '/' . $cleanPage) || request()->routeIs('*.' . $cleanPage) || request()->routeIs('school.landing.' . $cleanPage) || request()->routeIs('school.subdomain.' . $cleanPage);
        };

        // Prepare normalized notices list for landing and archive displays
        $noticesList = [];
        if ($recentNotices->count() > 0) {
            foreach ($recentNotices as $rn) {
                $priority = strtolower($rn->priority ?? 'normal');
                $tag = match($priority) {
                    'urgent'  => 'Urgent Announcement',
                    'exam'    => 'Examination Routine',
                    'holiday' => 'Holiday / Vacation',
                    default   => 'Academic Notice',
                };
                $badgeClass = match($priority) {
                    'urgent'  => 'bg-rose-100 text-rose-800 border-rose-200',
                    'exam'    => 'bg-amber-100 text-amber-900 border-amber-200',
                    'holiday' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                    default   => 'bg-slate-100 text-slate-800 border-slate-200',
                };

                $cleanNotice = strip_tags($rn->notice ?? '');
                $title = trim($rn->title ?: \Illuminate\Support\Str::limit($cleanNotice, 90));

                $noticesList[] = [
                    'id'              => $rn->id,
                    'title'           => $title,
                    'body'            => $rn->notice,
                    'body_clean'      => $cleanNotice,
                    'body_snippet'    => \Illuminate\Support\Str::limit($cleanNotice, 150),
                    'date'            => $rn->created_at ? $rn->created_at->format('M d, Y') : 'Recent',
                    'date_day'        => $rn->created_at ? $rn->created_at->format('d') : '01',
                    'date_month'      => $rn->created_at ? $rn->created_at->format('M') : 'Jan',
                    'date_short'      => $rn->created_at ? $rn->created_at->format('M d') : 'Recent',
                    'tag'             => $tag,
                    'priority'        => $priority,
                    'badge_class'     => $badgeClass,
                    'audience'        => ucfirst($rn->audience ?? 'All'),
                    'attachment_path' => $rn->attachment_path,
                    'attachment_url'  => $rn->attachment_path ? asset('storage/' . $rn->attachment_path) : null,
                    'is_db'           => true,
                ];
            }
        } elseif ($school->notice_board && strlen(trim($school->notice_board)) > 10) {
            $lines = array_filter(explode("\n", trim($school->notice_board)));
            foreach ($lines as $idx => $line) {
                $trimmed = trim($line);
                $tag = 'Official Notice';
                $badgeClass = 'bg-slate-100 text-slate-800 border-slate-200';
                $upper = strtoupper($trimmed);
                if (str_contains($upper, 'ADMISSION')) {
                    $tag = 'Admissions';
                    $badgeClass = 'bg-blue-100 text-blue-800 border-blue-200';
                } elseif (str_contains($upper, 'SCHOLARSHIP')) {
                    $tag = 'Scholarship';
                    $badgeClass = 'bg-purple-100 text-purple-800 border-purple-200';
                } elseif (str_contains($upper, 'EXAM') || str_contains($upper, 'ROUTINE')) {
                    $tag = 'Examination Routine';
                    $badgeClass = 'bg-amber-100 text-amber-900 border-amber-200';
                } elseif (str_contains($upper, 'HOLIDAY') || str_contains($upper, 'VACATION')) {
                    $tag = 'Holiday Notice';
                    $badgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                }

                $noticesList[] = [
                    'id'              => 'nb-' . ($idx + 1),
                    'title'           => $trimmed,
                    'body'            => nl2br(e($trimmed)),
                    'body_clean'      => $trimmed,
                    'body_snippet'    => \Illuminate\Support\Str::limit($trimmed, 150),
                    'date'            => date('M d, Y', strtotime('-' . ($idx * 2) . ' days')),
                    'date_day'        => date('d', strtotime('-' . ($idx * 2) . ' days')),
                    'date_month'      => date('M', strtotime('-' . ($idx * 2) . ' days')),
                    'date_short'      => date('M d', strtotime('-' . ($idx * 2) . ' days')),
                    'tag'             => $tag,
                    'priority'        => 'normal',
                    'badge_class'     => $badgeClass,
                    'audience'        => 'General Public',
                    'attachment_path' => null,
                    'attachment_url'  => null,
                    'is_db'           => false,
                ];
            }
        } else {
            $defaultNotices = [
                ['title' => 'Admissions Open for Academic Session 2026-2027 (+2 Science, Management & Law)', 'date' => date('M d, Y'), 'tag' => 'Admissions', 'badge_class' => 'bg-blue-100 text-blue-800 border-blue-200', 'desc' => 'Admissions are now open for the upcoming academic session. Merit scholarships available for SLC/SEE high scorers.'],
                ['title' => 'First Term Terminal Examination Routine Published for Grades XI & XII', 'date' => date('M d, Y', strtotime('-3 days')), 'tag' => 'Examination Routine', 'badge_class' => 'bg-amber-100 text-amber-900 border-amber-200', 'desc' => 'Students are advised to collect their admit cards and check the subject-wise timetable from the administration desk.'],
                ['title' => 'Annual Inter-House Sports Meet & Athletics Championship Schedule', 'date' => date('M d, Y', strtotime('-7 days')), 'tag' => 'Sports & ECA', 'badge_class' => 'bg-emerald-100 text-emerald-800 border-emerald-200', 'desc' => 'Registration is now active for track and field, football, basketball, and table tennis competitions.'],
                ['title' => 'National Science Olympiad & Project Exhibition Registrations Active', 'date' => date('M d, Y', strtotime('-12 days')), 'tag' => 'Academic Notice', 'badge_class' => 'bg-purple-100 text-purple-800 border-purple-200', 'desc' => 'Encouraging all innovative students to register their working models and science research projects before the deadline.'],
            ];
            foreach ($defaultNotices as $idx => $dn) {
                $noticesList[] = [
                    'id'              => 'def-' . ($idx + 1),
                    'title'           => $dn['title'],
                    'body'            => '<p>' . e($dn['desc']) . '</p>',
                    'body_clean'      => $dn['desc'],
                    'body_snippet'    => $dn['desc'],
                    'date'            => $dn['date'],
                    'date_day'        => date('d', strtotime($dn['date'])),
                    'date_month'      => date('M', strtotime($dn['date'])),
                    'date_short'      => date('M d', strtotime($dn['date'])),
                    'tag'             => $dn['tag'],
                    'priority'        => 'normal',
                    'badge_class'     => $dn['badge_class'],
                    'audience'        => 'All Students & Parents',
                    'attachment_path' => null,
                    'attachment_url'  => null,
                    'is_db'           => false,
                ];
            }
        }

        $topRankers = $school->getTopRankersData();

        return compact('school', 'teachers', 'committeeMembers', 'recentNotices', 'noticesList', 'events', 'classes', 'pageUrl', 'activeNav', 'topRankers');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SLUG-BASED MULTI-PAGE ROUTES (/school/{slug}/...)
    // ─────────────────────────────────────────────────────────────────────────

    public function show(string $slug)
    {
        $school = $this->resolveSchoolBySlug($slug);
        return view('school-landing.show', $this->getSchoolData($school));
    }

    public function about(string $slug)
    {
        $school = $this->resolveSchoolBySlug($slug);
        return view('school-landing.about', $this->getSchoolData($school));
    }

    public function academics(string $slug)
    {
        $school = $this->resolveSchoolBySlug($slug);
        return view('school-landing.academics', $this->getSchoolData($school));
    }

    public function admissions(string $slug)
    {
        $school = $this->resolveSchoolBySlug($slug);
        return view('school-landing.admissions', $this->getSchoolData($school));
    }

    public function notices(string $slug)
    {
        $school = $this->resolveSchoolBySlug($slug);
        return view('school-landing.notices', $this->getSchoolData($school));
    }

    public function noticeDetail(string $slug, $noticeId)
    {
        $school = $this->resolveSchoolBySlug($slug);
        $schoolData = $this->getSchoolData($school);

        $notice = \App\Models\Notice::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('school_id', $school->id)
            ->where(function($q) {
                $q->whereNull('audience')->orWhere('audience', '!=', 'teachers');
            })
            ->where('id', $noticeId)
            ->first();

        if (!$notice) {
            $fallback = collect($schoolData['noticesList'] ?? [])->firstWhere('id', (string)$noticeId);
            if (!$fallback) {
                return redirect($schoolData['pageUrl']('notices'));
            }
            $notice = (object) [
                'id'              => $fallback['id'],
                'title'           => $fallback['title'],
                'notice'          => $fallback['body'],
                'priority'        => $fallback['priority'] ?? 'normal',
                'audience'        => $fallback['audience'] ?? 'all',
                'attachment_path' => $fallback['attachment_path'] ?? null,
                'created_at'      => \Carbon\Carbon::parse($fallback['date'] ?? now()),
                'school'          => $school,
            ];
        }

        $relatedNotices = $school->notices()
            ->withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('id', '!=', $notice->id ?? 0)
            ->where(function($q) {
                $q->whereNull('audience')->orWhere('audience', '!=', 'teachers');
            })
            ->latest()
            ->take(5)
            ->get();

        return view('school-landing.notice-detail', array_merge($schoolData, compact('notice', 'relatedNotices')));
    }

    public function facilities(string $slug)
    {
        $school = $this->resolveSchoolBySlug($slug);
        return view('school-landing.facilities', $this->getSchoolData($school));
    }

    public function faculty(string $slug)
    {
        $school = $this->resolveSchoolBySlug($slug);
        return view('school-landing.faculty', $this->getSchoolData($school));
    }

    public function gallery(string $slug)
    {
        $school = $this->resolveSchoolBySlug($slug);
        return view('school-landing.gallery', $this->getSchoolData($school));
    }

    public function contact(string $slug)
    {
        $school = $this->resolveSchoolBySlug($slug);
        return view('school-landing.contact', $this->getSchoolData($school));
    }

    public function blogs(string $slug)
    {
        $school = $this->resolveSchoolBySlug($slug);
        $articles = $school->getLandingArticles('blog');
        $categories = array_values(array_unique(array_column($articles, 'category')));
        $activeType = 'blog';
        return view('school-landing.blogs', array_merge($this->getSchoolData($school), compact('articles', 'categories', 'activeType')));
    }

    public function news(string $slug)
    {
        $school = $this->resolveSchoolBySlug($slug);
        $articles = $school->getLandingArticles('news');
        $categories = array_values(array_unique(array_column($articles, 'category')));
        $activeType = 'news';
        return view('school-landing.news', array_merge($this->getSchoolData($school), compact('articles', 'categories', 'activeType')));
    }

    public function stories(string $slug)
    {
        $school = $this->resolveSchoolBySlug($slug);
        $articles = $school->getLandingArticles('story');
        $categories = array_values(array_unique(array_column($articles, 'category')));
        $activeType = 'story';
        return view('school-landing.stories', array_merge($this->getSchoolData($school), compact('articles', 'categories', 'activeType')));
    }

    public function publications(string $slug)
    {
        $school = $this->resolveSchoolBySlug($slug);
        $articles = $school->getLandingArticles();
        $categories = array_values(array_unique(array_column($articles, 'category')));
        $activeType = 'all';
        return view('school-landing.publications', array_merge($this->getSchoolData($school), compact('articles', 'categories', 'activeType')));
    }

    public function articleDetail(string $slug, string $articleSlug)
    {
        $school = $this->resolveSchoolBySlug($slug);
        $article = $school->getLandingArticleBySlug($articleSlug);
        if (!$article) {
            abort(404, 'Article not found');
        }
        $relatedArticles = array_slice(array_values(array_filter($school->getLandingArticles($article['type']), function($a) use ($article) {
            return $a['slug'] !== $article['slug'];
        })), 0, 3);
        return view('school-landing.article-detail', array_merge($this->getSchoolData($school), compact('article', 'relatedArticles')));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SUBDOMAIN-BASED MULTI-PAGE ROUTES ({subdomain}.nshlms.com/...)
    // ─────────────────────────────────────────────────────────────────────────

    public function showBySubdomain(string $subdomain, ?string $slug = null)
    {
        if (in_array(strtolower($subdomain), ['www', 'app', 'admin', 'super', 'mail', 'api', 'blog', 'support', 'classroom'])) {
            return redirect()->route('saas.landing');
        }
        $school = $this->resolveSchoolBySubdomain($subdomain);
        return view('school-landing.show', $this->getSchoolData($school));
    }

    public function aboutBySubdomain(string $subdomain)
    {
        $school = $this->resolveSchoolBySubdomain($subdomain);
        return view('school-landing.about', $this->getSchoolData($school));
    }

    public function academicsBySubdomain(string $subdomain)
    {
        $school = $this->resolveSchoolBySubdomain($subdomain);
        return view('school-landing.academics', $this->getSchoolData($school));
    }

    public function admissionsBySubdomain(string $subdomain)
    {
        $school = $this->resolveSchoolBySubdomain($subdomain);
        return view('school-landing.admissions', $this->getSchoolData($school));
    }

    public function noticesBySubdomain(string $subdomain)
    {
        $school = $this->resolveSchoolBySubdomain($subdomain);
        return view('school-landing.notices', $this->getSchoolData($school));
    }

    public function noticeDetailBySubdomain(string $subdomain, $noticeId)
    {
        $school = $this->resolveSchoolBySubdomain($subdomain);
        $schoolData = $this->getSchoolData($school);

        $notice = \App\Models\Notice::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('school_id', $school->id)
            ->where(function($q) {
                $q->whereNull('audience')->orWhere('audience', '!=', 'teachers');
            })
            ->where('id', $noticeId)
            ->first();

        if (!$notice) {
            $fallback = collect($schoolData['noticesList'] ?? [])->firstWhere('id', (string)$noticeId);
            if (!$fallback) {
                return redirect($schoolData['pageUrl']('notices'));
            }
            $notice = (object) [
                'id'              => $fallback['id'],
                'title'           => $fallback['title'],
                'notice'          => $fallback['body'],
                'priority'        => $fallback['priority'] ?? 'normal',
                'audience'        => $fallback['audience'] ?? 'all',
                'attachment_path' => $fallback['attachment_path'] ?? null,
                'created_at'      => \Carbon\Carbon::parse($fallback['date'] ?? now()),
                'school'          => $school,
            ];
        }

        $relatedNotices = $school->notices()
            ->withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('id', '!=', $notice->id ?? 0)
            ->where(function($q) {
                $q->whereNull('audience')->orWhere('audience', '!=', 'teachers');
            })
            ->latest()
            ->take(5)
            ->get();

        return view('school-landing.notice-detail', array_merge($schoolData, compact('notice', 'relatedNotices')));
    }

    public function facilitiesBySubdomain(string $subdomain)
    {
        $school = $this->resolveSchoolBySubdomain($subdomain);
        return view('school-landing.facilities', $this->getSchoolData($school));
    }

    public function facultyBySubdomain(string $subdomain)
    {
        $school = $this->resolveSchoolBySubdomain($subdomain);
        return view('school-landing.faculty', $this->getSchoolData($school));
    }

    public function galleryBySubdomain(string $subdomain)
    {
        $school = $this->resolveSchoolBySubdomain($subdomain);
        return view('school-landing.gallery', $this->getSchoolData($school));
    }

    public function contactBySubdomain(string $subdomain)
    {
        $school = $this->resolveSchoolBySubdomain($subdomain);
        return view('school-landing.contact', $this->getSchoolData($school));
    }

    public function blogsBySubdomain(string $subdomain)
    {
        $school = $this->resolveSchoolBySubdomain($subdomain);
        $articles = $school->getLandingArticles('blog');
        $categories = array_values(array_unique(array_column($articles, 'category')));
        $activeType = 'blog';
        return view('school-landing.blogs', array_merge($this->getSchoolData($school), compact('articles', 'categories', 'activeType')));
    }

    public function newsBySubdomain(string $subdomain)
    {
        $school = $this->resolveSchoolBySubdomain($subdomain);
        $articles = $school->getLandingArticles('news');
        $categories = array_values(array_unique(array_column($articles, 'category')));
        $activeType = 'news';
        return view('school-landing.news', array_merge($this->getSchoolData($school), compact('articles', 'categories', 'activeType')));
    }

    public function storiesBySubdomain(string $subdomain)
    {
        $school = $this->resolveSchoolBySubdomain($subdomain);
        $articles = $school->getLandingArticles('story');
        $categories = array_values(array_unique(array_column($articles, 'category')));
        $activeType = 'story';
        return view('school-landing.stories', array_merge($this->getSchoolData($school), compact('articles', 'categories', 'activeType')));
    }

    public function publicationsBySubdomain(string $subdomain)
    {
        $school = $this->resolveSchoolBySubdomain($subdomain);
        $articles = $school->getLandingArticles();
        $categories = array_values(array_unique(array_column($articles, 'category')));
        $activeType = 'all';
        return view('school-landing.publications', array_merge($this->getSchoolData($school), compact('articles', 'categories', 'activeType')));
    }

    public function articleDetailBySubdomain(string $subdomain, string $articleSlug)
    {
        $school = $this->resolveSchoolBySubdomain($subdomain);
        $article = $school->getLandingArticleBySlug($articleSlug);
        if (!$article) {
            abort(404, 'Article not found');
        }
        $relatedArticles = array_slice(array_values(array_filter($school->getLandingArticles($article['type']), function($a) use ($article) {
            return $a['slug'] !== $article['slug'];
        })), 0, 3);
        return view('school-landing.article-detail', array_merge($this->getSchoolData($school), compact('article', 'relatedArticles')));
    }

    /**
     * Handle public newsletter subscription for a school.
     */
    public function newsletterSubscribe(Request $request, string $slug)
    {
        $school = $this->resolveSchoolBySlug($slug);
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $settings = $school->settings ?? [];
        $subscribers = $settings['newsletter_subscribers'] ?? [];
        $email = strtolower(trim($request->input('email')));

        if (!in_array($email, $subscribers)) {
            $subscribers[] = $email;
            $settings['newsletter_subscribers'] = array_slice($subscribers, -500);
            $school->settings = $settings;
            $school->save();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you for subscribing! You will receive our latest academic dispatches and newsletters directly.',
            ]);
        }

        return back()->with('success', 'Thank you for subscribing to our newsletter!');
    }

    /**
     * Preview the landing page for the current user's school (authenticated).
     */
    public function preview()
    {
        $school = auth()->user()->school;
        
        if (!$school) {
            abort(404, 'School not found');
        }

        $host = strtolower(request()->getHost());
        if (in_array($host, ['localhost', '127.0.0.1']) || str_contains(config('app.url', ''), 'localhost')) {
            return redirect()->route('school.landing', ['slug' => $school->slug]);
        }

        return redirect()->to($school->subdomain_url ?: route('school.landing', ['slug' => $school->slug]));
    }
}
