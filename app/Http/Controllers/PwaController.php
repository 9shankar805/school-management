<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PwaController extends Controller
{
    /**
     * Generate dynamic Web App Manifest (PWA) tailored to the active school.
     * When a user belongs to or is viewing a specific school, the PWA installs
     * with that school's name, custom uploaded logo, and theme color.
     */
    public function manifest(Request $request): JsonResponse
    {
        $school = $this->resolveSchool($request);

        $name = $school?->name ?? config('app.name', 'NepalSchoolHub ERP');
        $shortName = !empty($school?->name) 
            ? Str::limit($school->name, 14, '') 
            : config('app.name', 'SchoolHub');
        $description = $school?->description 
            ?: "Official school management application for {$name}. Attendance, routine, notices, grades, and exams.";
        $themeColor = $school?->primary_color ?: '#6366f1';
        $bgColor = '#0f172a';

        $icons = $this->buildIcons($school);
        $shortcuts = $this->buildShortcuts($school, $icons[0]['src'] ?? asset('imgs/logo_icon_192.webp'));

        $manifest = [
            'name'             => $name,
            'short_name'       => $shortName,
            'description'      => $description,
            'start_url'        => url('/home'),
            'scope'            => url('/'),
            'display'          => 'standalone',
            'orientation'      => 'portrait-primary',
            'background_color' => $bgColor,
            'theme_color'      => $themeColor,
            'lang'             => app()->getLocale() ?: 'en',
            'categories'       => ['education', 'productivity', 'business'],
            'icons'            => $icons,
            'shortcuts'        => $shortcuts,
            'screenshots'      => [],
            'related_applications' => [],
            'prefer_related_applications' => false,
        ];

        return response()->json($manifest, 200, [
            'Content-Type'  => 'application/manifest+json; charset=utf-8',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }

    /**
     * Resolve the active school from user auth, query parameter, session, or database.
     */
    protected function resolveSchool(Request $request): ?School
    {
        // 1. Explicit school_id in query (e.g. ?school_id=1)
        if ($request->filled('school_id')) {
            $school = School::find($request->query('school_id'));
            if ($school) return $school;
        }

        // 2. Explicit school slug in query (e.g. ?school=xyz)
        if ($request->filled('school')) {
            $school = School::where('slug', $request->query('school'))->first();
            if ($school) return $school;
        }

        // 3. Authenticated user's associated school
        if ($request->user() && $request->user()->school_id) {
            $school = $request->user()->school;
            if ($school) return $school;
        }

        // 4. Session school
        $sessionSchoolId = session('current_school_id') ?? session('school_id');
        if ($sessionSchoolId) {
            $school = School::find($sessionSchoolId);
            if ($school) return $school;
        }

        // 5. Fallback to the first active school in database
        return School::active()->first() ?? School::first();
    }

    /**
     * Build icon array for the manifest.
     * Uses the school's uploaded logo if present, else falls back to default app icons.
     */
    protected function buildIcons(?School $school): array
    {
        if ($school && !empty($school->logo)) {
            $logoPath = $school->logo;
            $logoUrl = asset('storage/' . ltrim(str_replace('storage/', '', $logoPath), '/'));

            // Detect MIME type based on extension
            $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
            $mime = match ($ext) {
                'webp' => 'image/webp',
                'svg'  => 'image/svg+xml',
                'jpg', 'jpeg' => 'image/jpeg',
                default => 'image/png',
            };

            return [
                [
                    'src'     => $logoUrl,
                    'sizes'   => '512x512',
                    'type'    => $mime,
                    'purpose' => 'any maskable',
                ],
                [
                    'src'     => $logoUrl,
                    'sizes'   => '192x192',
                    'type'    => $mime,
                    'purpose' => 'any maskable',
                ],
                [
                    'src'     => $logoUrl,
                    'sizes'   => '128x128',
                    'type'    => $mime,
                    'purpose' => 'any',
                ],
                [
                    'src'     => $logoUrl,
                    'sizes'   => '64x64',
                    'type'    => $mime,
                    'purpose' => 'any',
                ],
                [
                    'src'     => $logoUrl,
                    'sizes'   => '32x32',
                    'type'    => $mime,
                    'purpose' => 'any',
                ],
            ];
        }

        // Default application fallback icons
        return [
            [
                'src'     => asset('imgs/logo_icon_512.webp'),
                'sizes'   => '512x512',
                'type'    => 'image/webp',
                'purpose' => 'any maskable',
            ],
            [
                'src'     => asset('imgs/logo_icon_192.webp'),
                'sizes'   => '192x192',
                'type'    => 'image/webp',
                'purpose' => 'any',
            ],
            [
                'src'     => asset('imgs/logo_icon_64.webp'),
                'sizes'   => '64x64',
                'type'    => 'image/webp',
            ],
            [
                'src'     => asset('imgs/logo_icon_32.webp'),
                'sizes'   => '32x32',
                'type'    => 'image/webp',
            ],
        ];
    }

    /**
     * Build app shortcuts (jump links on long press).
     */
    protected function buildShortcuts(?School $school, string $iconUrl): array
    {
        return [
            [
                'name'        => 'Dashboard',
                'short_name'  => 'Home',
                'description' => 'Open the main dashboard',
                'url'         => url('/home'),
                'icons'       => [['src' => $iconUrl, 'sizes' => '192x192', 'type' => 'image/webp']],
            ],
            [
                'name'        => 'Attendance',
                'short_name'  => 'Attendance',
                'description' => "Mark or view attendance",
                'url'         => url('/attendances/take'),
                'icons'       => [['src' => $iconUrl, 'sizes' => '192x192', 'type' => 'image/webp']],
            ],
            [
                'name'        => 'Routine',
                'short_name'  => 'Routine',
                'description' => 'View class timetable',
                'url'         => url('/routine/view'),
                'icons'       => [['src' => $iconUrl, 'sizes' => '192x192', 'type' => 'image/webp']],
            ],
            [
                'name'        => 'Notices',
                'short_name'  => 'Notices',
                'description' => 'View school notices and circulars',
                'url'         => url('/notices'),
                'icons'       => [['src' => $iconUrl, 'sizes' => '192x192', 'type' => 'image/webp']],
            ],
        ];
    }
}
