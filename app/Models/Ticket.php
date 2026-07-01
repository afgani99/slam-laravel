<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';

    public const STATUS_PENDING = 'pending';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_CANCELLED = 'cancelled';

    public const CASE_LINK_DOWN = 'Link Down';

    public const CASE_LINK_HIGH_LATENCY = 'Link High Latency';

    public const CASE_LINK_INTERMITTENT = 'Link Intermittent';

    public const CASE_CAPACITY_DROP = 'Kapasitas Drop';

    public const STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_PENDING,
        self::STATUS_CLOSED,
        self::STATUS_CANCELLED,
    ];

    public const CASE_TYPES = [
        self::CASE_LINK_DOWN,
        self::CASE_LINK_HIGH_LATENCY,
        self::CASE_LINK_INTERMITTENT,
        self::CASE_CAPACITY_DROP,
    ];

    protected $fillable = [
        'ticket_number',
        'gamas_id',
        'cid_id',
        'vendor_ticket_number',
        'case_type',
        'started_at',
        'finished_at',
        'rfo_action',
        'status',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function cid(): BelongsTo
    {
        return $this->belongsTo(Cid::class);
    }

    public function gamas(): BelongsTo
    {
        return $this->belongsTo(Gamas::class);
    }

    public function pendingIntervals(): HasMany
    {
        return $this->hasMany(TicketPendingInterval::class);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function countsForSla(): bool
    {
        return $this->isClosed() && $this->case_type === self::CASE_LINK_DOWN;
    }
}
