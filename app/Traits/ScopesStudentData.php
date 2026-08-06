<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Trait for API controllers that handle student-scoped data.
 *
 * Usage:
 *   use ScopesStudentData;
 *
 *   // In any method that fetches student-scoped rows:
 *   $ids = $this->ownedStudentIds($request);   // null = no restriction
 *   $query->when($ids !== null, fn($q) => $q->whereIn('student_id', $ids));
 *
 *   // Or guard a single student show() before hitting the DB:
 *   if ($denied = $this->denyIfNotOwned($request, $studentId)) return $denied;
 */
trait ScopesStudentData
{
    /**
     * Returns the array of allowed student IDs for the current request,
     * or null if the caller has unrestricted access (admin / teacher / etc.).
     *
     * @return int[]|null
     */
    protected function ownedStudentIds(Request $request): ?array
    {
        return $request->get('_owned_student_ids'); // set by ScopeToOwnData middleware
    }

    /**
     * Apply a whereIn('student_id', ...) scope to a query only when the
     * caller is restricted (student or parent role).
     *
     * @param  Builder    $query
     * @param  Request    $request
     * @param  string     $column  Column name to scope on (default: 'student_id')
     * @return Builder
     */
    protected function applyStudentScope(Builder $query, Request $request, string $column = 'student_id'): Builder
    {
        $ids = $this->ownedStudentIds($request);

        if ($ids !== null) {
            $query->whereIn($column, $ids);
        }

        return $query;
    }

    /**
     * For single-resource endpoints (show / update / delete):
     * Returns a 403 JSON response when the given $studentId is not in the
     * caller's owned list. Returns null when access is allowed.
     *
     * @param  Request  $request
     * @param  int      $studentId
     * @return JsonResponse|null
     */
    protected function denyIfNotOwned(Request $request, int $studentId): ?JsonResponse
    {
        $ids = $this->ownedStudentIds($request);

        if ($ids !== null && ! in_array($studentId, $ids, true)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'You do not have permission to access this record.',
            ], 403);
        }

        return null;
    }
}
