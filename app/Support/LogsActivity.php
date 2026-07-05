<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    protected function logActivity(string $action, string $description, ?Model $model = null, array $params = []): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description . (count($params) ? '|' . json_encode($params) : ''),
            'model_type' => $model ? $model::class : null,
            'model_id' => $model?->getKey(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
