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
    ];

    protected function casts(): array
    {
        return [
            'sla_percentage' => 'decimal:2',
        ];
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
