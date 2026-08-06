<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Row-level security for the API layer.
 *
 * Students  → may only query their own student record, attendance, and marks.
 * Parents   → may only query records belonging to their linked children.
 * All others pass through unchanged (admins, teachers, etc. are governed
 * by Spatie permission checks in the individual controllers).
 *
 * The resolved "allowed student IDs" are stashed on the request as
 *   $request->ownedStudentIds   (array of int)
 *
 * Controllers must call $this->scopedStudentIds($request) from the
 * ScopesStudentData trait (or read $request->ownedStudentIds directly)
 * to filter queries appropriately.
 *
 * Register as named middleware 'scope.own' in Kernel.php and apply it
 * to any API route that returns student-scoped data.
 */
class ScopeToOwnData
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($user->isStudent()) {
            // A student may only see their own record.
            $request->merge(['_owned_student_ids' => [$user->id]]);

        } elseif ($user->isParent()) {
            // A parent may see all their linked children.
            $childIds = $user->children()->pluck('users.id')->toArray();
            $request->merge(['_owned_student_ids' => $childIds]);

        } else {
            // Staff / admin — no restriction injected.
            $request->merge(['_owned_student_ids' => null]);
        }

        return $next($request);
    }
}
