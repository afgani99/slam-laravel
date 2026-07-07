<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cid extends Model
{
    use HasFactory;

    protected $fillable = [
        'cid',
        'cid_is',
        'vendor_name',
        'customer_name',
        'service',
        'sla_percentage',
        'is_dismantled',
        'dismantled_at',
        'dismantled_by',
    ];

    protected function casts(): array
    {
        return [
            'sla_percentage' => 'decimal:2',
            'is_dismantled' => 'boolean',
            'dismantled_at' => 'datetime',
        ];
    }

    public function dismantledBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'dismantled_by');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
