<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    protected static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            static::logActivity($model, 'created');
        });

        static::updated(function ($model) {
            static::logActivity($model, 'updated', [
                'before' => $model->getOriginal(),
                'after'  => $model->getDirty(),
            ]);
        });

        static::deleted(function ($model) {
            static::logActivity($model, $model->isForceDeleting() ?? false ? 'force_deleted' : 'deleted');
        });

        static::restored(function ($model) {
            static::logActivity($model, 'restored');
        });
    }

    private static function logActivity($model, string $action, ?array $changes = null): void
    {
        // Skip logging during tests seeding or unauthenticated contexts
        if (!app()->runningInConsole() || auth()->check()) {
            ActivityLog::create([
                'user_id'    => auth()->id(),
                'action'     => $action,
                'model_type' => get_class($model),
                'model_id'   => $model->getKey(),
                'changes'    => $changes,
                'ip_address' => request()->ip(),
            ]);
        }
    }
}
