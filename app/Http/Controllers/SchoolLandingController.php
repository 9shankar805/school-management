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
        $recentNotices = $school->notices()->latest()->take(20)->get();
        $events = \App\Models\Event::where('school_id', $school->id)->latest()->take(20)->get();
        $classes = \App\Models\SchoolClass::where('school_id', $school->id)->with(['sections', 'courses'])->take(20)->get();

        // Smart Multi-Page Link Generator
        $isSubdomain = request()->routeIs('school.subdomain*') || !str_contains(request()->path(), 'school/');
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

        return compact('school', 'teachers', 'committeeMembers', 'recentNotices', 'events', 'classes', 'pageUrl', 'activeNav');
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

    /**
     * Preview the landing page for the current user's school (authenticated).
     */
    public function preview()
    {
        $school = auth()->user()->school;
        
        if (!$school) {
            abort(404, 'School not found');
        }

        return redirect()->to($school->subdomain_url);
    }
}
