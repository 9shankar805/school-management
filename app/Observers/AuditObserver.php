<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

/**
 * AuditObserver — register this on any Eloquent model to get automatic
 * audit trails on created / updated / deleted / restored events.
 *
 * Registration (in AppServiceProvider or individual model's boot):
 *   User::observe(AuditObserver::class);
 *
 * Or register in bulk via App\Providers\ObserverServiceProvider.
 */
class AuditObserver
{
    public function created(Model $model): void
    {
        $this->log('created', $model, [], $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $dirty = $model->getDirty();

        // Don't log if only timestamps changed
        unset($dirty['updated_at'], $dirty['created_at']);
        if (empty($dirty)) {
            return;
        }

        $oldValues = array_intersect_key($model->getOriginal(), $dirty);
        $this->log('updated', $model, $oldValues, $dirty);
    }

    public function deleted(Model $model): void
    {
        $this->log('deleted', $model, $model->getAttributes(), []);
    }

    public function restored(Model $model): void
    {
        $this->log('restored', $model, [], $model->getAttributes());
    }

    // -----------------------------------------------------------------------

    private function log(string $event, Model $model, array $old, array $new): void
    {
        try {
            AuditLog::create([
                'user_id'        => auth()->id(),
                'user_type'      => auth()->check() ? get_class(auth()->user()) : null,
                'event'          => $event,
                'auditable_type' => get_class($model),
                'auditable_id'   => $model->getKey(),
                'old_values'     => $old,
                'new_values'     => $new,
                'url'            => request()?->fullUrl(),
                'ip_address'     => request()?->ip(),
                'user_agent'     => request()?->userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Never let audit logging break the main flow
            logger()->error('AuditObserver failed: ' . $e->getMessage());
        }
    }
}
