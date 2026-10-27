<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * GET /api/v1/settings
     * Returns all public settings (or all if admin).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Setting::query();

        if (! $request->user()->hasRole('admin')) {
            $query->where('is_public', true);
        }

        $settings = $query->get()->groupBy('group');

        return response()->json(['status' => 'success', 'data' => $settings]);
    }

    /**
     * GET /api/v1/settings/{group}
     */
    public function group(Request $request, string $group): JsonResponse
    {
        $settings = Setting::where('group', $group)->get();

        return response()->json(['status' => 'success', 'data' => $settings]);
    }

    /**
     * POST /api/v1/settings — upsert one or many settings
     * Body: [ { key, value, group?, type? }, ... ] or single object
     */
    public function upsert(Request $request): JsonResponse
    {
        $request->validate([
            '*.key'   => ['required', 'string', 'max:100'],
            '*.value' => ['nullable'],
            '*.group' => ['sometimes', 'string', 'max:60'],
            '*.type'  => ['sometimes', 'in:string,boolean,integer,json,file'],
        ]);

        $items = is_array($request->all()[0] ?? null) ? $request->all() : [$request->all()];

        foreach ($items as $item) {
            Setting::set(
                key: $item['key'],
                value: $item['value'] ?? null,
                group: $item['group'] ?? 'general',
            );
        }

        return response()->json(['status' => 'success', 'message' => 'Settings saved.']);
    }

    /**
     * GET /api/v1/search
     * Global search for students, users, etc.
     */
    public function globalSearch(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $limit = min($request->get('limit', 10), 50);

        \Log::info('Global search called', ['query' => $query, 'user_id' => $request->user()?->id, 'school_id' => $request->user()?->school_id]);

        if (strlen($query) < 2) {
            return response()->json(['status' => 'success', 'data' => []]);
        }

        $results = [];
        $user = $request->user();
        $schoolId = $user?->school_id ?? null;

        // ── 1. If User is a STUDENT ──────────────────────────────────────────
        if ($user && $user->isStudent()) {
            // Self Profile match
            if (stripos($user->full_name, $query) !== false || stripos($user->email, $query) !== false) {
                $results[] = [
                    'type'     => 'profile',
                    'label'    => 'My Student Profile (' . $user->full_name . ')',
                    'sublabel' => 'View my personal information, records & fees',
                    'href'     => "/students/view/profile/{$user->id}",
                    'icon'     => 'bi-person-circle',
                ];
            }

            // Search Notices
            $noticesQuery = \App\Models\Notice::where('title', 'like', "%{$query}%")
                ->orWhere('notice', 'like', "%{$query}%");
            if ($schoolId) {
                $noticesQuery->where(function($q) use ($schoolId) {
                    $q->where('school_id', $schoolId)->orWhereNull('school_id');
                });
            }
            foreach ($noticesQuery->limit(5)->get() as $notice) {
                $results[] = [
                    'type'     => 'notice',
                    'label'    => $notice->title,
                    'sublabel' => 'Notice — ' . ($notice->created_at ? $notice->created_at->format('M d, Y') : ''),
                    'href'     => "/notices",
                    'icon'     => 'bi-megaphone',
                ];
            }

            // Search Homework
            $hwQuery = \App\Models\Homework::where('title', 'like', "%{$query}%");
            if ($schoolId) {
                $hwQuery->where('school_id', $schoolId);
            }
            foreach ($hwQuery->limit(5)->get() as $hw) {
                $results[] = [
                    'type'     => 'homework',
                    'label'    => $hw->title,
                    'sublabel' => 'Homework Assignment',
                    'href'     => "/homework",
                    'icon'     => 'bi-journal-text',
                ];
            }

            // Search Library Books
            $booksQuery = \App\Models\Book::where('title', 'like', "%{$query}%")
                ->orWhere('author', 'like', "%{$query}%");
            if ($schoolId) {
                $booksQuery->where('school_id', $schoolId);
            }
            foreach ($booksQuery->limit(5)->get() as $book) {
                $results[] = [
                    'type'     => 'book',
                    'label'    => $book->title,
                    'sublabel' => 'Library Book — ' . ($book->author ?? 'Author'),
                    'href'     => "/library",
                    'icon'     => 'bi-book',
                ];
            }

            return response()->json(['status' => 'success', 'data' => array_slice($results, 0, $limit)]);
        }

        // ── 2. If User is a PARENT ───────────────────────────────────────────
        if ($user && $user->isParent()) {
            // Search linked children
            if (method_exists($user, 'children')) {
                $children = $user->children()
                    ->where(function ($q) use ($query) {
                        $q->where('first_name', 'like', "%{$query}%")
                          ->orWhere('last_name', 'like', "%{$query}%");
                    })
                    ->get();

                foreach ($children as $child) {
                    $results[] = [
                        'type'     => 'child',
                        'label'    => $child->full_name . ' (My Child)',
                        'sublabel' => 'View child attendance & reports',
                        'href'     => "/parent/{$child->id}/attendance",
                        'icon'     => 'bi-person-badge',
                    ];
                }
            }

            // Search Notices
            $noticesQuery = \App\Models\Notice::where('title', 'like', "%{$query}%");
            if ($schoolId) {
                $noticesQuery->where('school_id', $schoolId);
            }
            foreach ($noticesQuery->limit(5)->get() as $notice) {
                $results[] = [
                    'type'     => 'notice',
                    'label'    => $notice->title,
                    'sublabel' => 'School Notice',
                    'href'     => "/notices",
                    'icon'     => 'bi-megaphone',
                ];
            }

            return response()->json(['status' => 'success', 'data' => array_slice($results, 0, $limit)]);
        }

        // ── 3. STAFF / TEACHERS / ADMINS / COORDINATORS ──────────────────────
        $isClassQuery = in_array(strtolower($query), ['class', 'classes', 'grade', 'grades', 'sec', 'section', 'sections', 'classroom']);
        $isCourseQuery = in_array(strtolower($query), ['course', 'courses', 'subject', 'subjects', 'curriculum', 'book']);
        $isExamQuery = in_array(strtolower($query), ['exam', 'exams', 'test', 'examination', 'marks', 'result', 'gradebook']);
        $isDeptQuery = in_array(strtolower($query), ['dept', 'department', 'departments', 'academic dept']);
        $isProgQuery = in_array(strtolower($query), ['program', 'programs', 'stream', 'faculty']);

        // 3a. Search Departments
        if (class_exists(\App\Models\Department::class)) {
            $deptQ = \App\Models\Department::query();
            if (!$isDeptQuery) {
                $deptQ->where(function($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('code', 'like', "%{$query}%");
                });
            }
            if ($schoolId) {
                $deptQ->where('school_id', $schoolId);
            }
            foreach ($deptQ->limit(5)->get() as $dept) {
                $results[] = [
                    'type'     => 'department',
                    'label'    => $dept->name,
                    'sublabel' => 'Academic Department (' . ($dept->code ?? 'Dept') . ')',
                    'href'     => "/departments",
                    'icon'     => 'bi-building',
                ];
            }
        }

        // 3b. Search Programs
        if (class_exists(\App\Models\Program::class)) {
            $progQ = \App\Models\Program::query();
            if (!$isProgQuery) {
                $progQ->where(function($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('code', 'like', "%{$query}%");
                });
            }
            if ($schoolId) {
                $progQ->where('school_id', $schoolId);
            }
            foreach ($progQ->limit(5)->get() as $prog) {
                $results[] = [
                    'type'     => 'program',
                    'label'    => $prog->name,
                    'sublabel' => 'Academic Program / Stream',
                    'href'     => "/programs",
                    'icon'     => 'bi-mortarboard-fill',
                ];
            }
        }

        // 3c. Search Classes & Grades
        $classesQuery = \App\Models\SchoolClass::query();
        if (!$isClassQuery) {
            $classesQuery->where('class_name', 'like', "%{$query}%");
        }
        if ($schoolId) {
            $classesQuery->where('school_id', $schoolId);
        }
        foreach ($classesQuery->limit(6)->get() as $c) {
            $results[] = [
                'type'     => 'class',
                'label'    => $c->class_name,
                'sublabel' => 'Academic Class & Grade',
                'href'     => "/classes",
                'icon'     => 'bi-diagram-3-fill',
            ];
        }

        // 3d. Search Courses & Subjects
        $coursesQuery = \App\Models\Course::query();
        if (!$isCourseQuery) {
            $coursesQuery->where(function ($q) use ($query) {
                $q->where('course_name', 'like', "%{$query}%")
                  ->orWhere('course_type', 'like', "%{$query}%");
            });
        }
        $coursesQuery->with('schoolClass');
        if ($schoolId) {
            $coursesQuery->where('school_id', $schoolId);
        }
        foreach ($coursesQuery->limit(6)->get() as $course) {
            $className = $course->schoolClass?->class_name ? ' (' . $course->schoolClass->class_name . ')' : '';
            $results[] = [
                'type'     => 'course',
                'label'    => $course->course_name . $className,
                'sublabel' => 'Subject — ' . ($course->course_type ?? 'Core'),
                'href'     => "/classes",
                'icon'     => 'bi-book-fill',
            ];
        }

        // 3e. Search Exams
        if (class_exists(\App\Models\Exam::class)) {
            $examQ = \App\Models\Exam::query();
            if (!$isExamQuery) {
                $examQ->where('exam_name', 'like', "%{$query}%");
            }
            if ($schoolId) {
                $examQ->where('school_id', $schoolId);
            }
            foreach ($examQ->latest()->limit(4)->get() as $exam) {
                $results[] = [
                    'type'     => 'exam',
                    'label'    => $exam->exam_name ?? 'Examination',
                    'sublabel' => 'Exam Schedule & Marks',
                    'href'     => "/exams/view",
                    'icon'     => 'bi-file-earmark-text-fill',
                ];
            }
        }

        // 3d. Search Students
        $studentsQuery = \App\Models\User::whereHas('roles', function ($q) {
            $q->where('name', 'student');
        })
        ->where(function ($q) use ($query) {
            $q->where('first_name', 'like', "%{$query}%")
              ->orWhere('last_name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%");
        })
        ->with('academic_info');

        if ($schoolId) {
            $studentsQuery->where('school_id', $schoolId);
        }

        $students = $studentsQuery->limit($limit)->get();

        foreach ($students as $student) {
            $results[] = [
                'type'     => 'student',
                'label'    => $student->full_name,
                'sublabel' => $student->academic_info->admission_no ?? 'Student',
                'href'     => "/students/view/profile/{$student->id}",
                'icon'     => 'bi-person',
            ];
        }

        // 3e. Search Teachers & Staff
        $staffQuery = \App\Models\User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['teacher', 'admin', 'principal', 'vice-principal', 'academic-coordinator', 'accountant', 'hr']);
        })
        ->where(function ($q) use ($query) {
            $q->where('first_name', 'like', "%{$query}%")
              ->orWhere('last_name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%");
        });

        if ($schoolId) {
            $staffQuery->where('school_id', $schoolId);
        }

        $staff = $staffQuery->limit($limit)->get();

        foreach ($staff as $person) {
            $results[] = [
                'type'     => 'staff',
                'label'    => $person->full_name,
                'sublabel' => ucfirst(str_replace('-', ' ', $person->primary_role)),
                'href'     => "/teachers/view/profile/{$person->id}",
                'icon'     => 'bi-person-badge',
            ];
        }

        // 3f. Search Notices
        $noticesQuery = \App\Models\Notice::where('title', 'like', "%{$query}%");
        if ($schoolId) {
            $noticesQuery->where('school_id', $schoolId);
        }
        foreach ($noticesQuery->limit(4)->get() as $notice) {
            $results[] = [
                'type'     => 'notice',
                'label'    => $notice->title,
                'sublabel' => 'Notice / Circular',
                'href'     => "/notice/create",
                'icon'     => 'bi-megaphone',
            ];
        }

        return response()->json(['status' => 'success', 'data' => array_slice($results, 0, $limit)]);
    }
}
