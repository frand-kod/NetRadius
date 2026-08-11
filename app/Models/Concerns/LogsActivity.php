<?php

namespace App\Models\Concerns;

use App\Services\ActivityLogger;

trait LogsActivity
{
    protected static function bootLogsActivity(): void
    {
        static::created(fn ($model) => ActivityLogger::log(
            'create',
            class_basename($model)." #{$model->getKey()} created"
        ));

        static::updated(fn ($model) => ActivityLogger::log(
            'update',
            class_basename($model)." #{$model->getKey()} updated"
        ));

        static::deleted(fn ($model) => ActivityLogger::log(
            'delete',
            class_basename($model)." #{$model->getKey()} deleted"
        ));
    }
}
