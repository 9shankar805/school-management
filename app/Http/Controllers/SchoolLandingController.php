<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolLandingController extends Controller
{
    /**
     * Show the public landing page for a school.
     */
    public function show(string $slug): View
    {
        $school = School::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();
        $teachers = $school->users()->where('role', 'teacher')->take(8)->get();

        return view('school-landing.show', compact('school', 'teachers'));
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

        return redirect()->route('school.landing', ['slug' => $school->slug]);
    }
}
