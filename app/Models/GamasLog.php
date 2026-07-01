<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamasLog extends Model
{
    const TYPE_PENDING = 'pending';
    const TYPE_RESUME = 'resume';

    protected $fillable = [
        'gamas_id',
        'type',
        'reason',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function gamas(): BelongsTo
    {
        return $this->belongsTo(Gamas::class);
    }
}
