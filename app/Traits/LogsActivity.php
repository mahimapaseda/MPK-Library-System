<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Request;

trait LogsActivity
{
    protected static function booted()
    {
        static::created(function ($model) {
            $model->logAction('created', "Created {$model->getActivityDescription()}");
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            unset($changes['updated_at']);
            
            if (empty($changes)) return;

            $model->logAction('updated', "Updated {$model->getActivityDescription()}", [
                'old' => array_intersect_key($model->getOriginal(), $changes),
                'new' => $changes,
            ]);
        });

        static::deleted(function ($model) {
            $model->logAction('deleted', "Deleted {$model->getActivityDescription()}");
        });
    }

    public function logAction(string $action, string $description, array $properties = null)
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => get_class($this),
            'model_id' => $this->id,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => Request::ip(),
        ]);
    }

    protected function getActivityDescription(): string
    {
        return class_basename($this) . " #{$this->id}";
    }
}
