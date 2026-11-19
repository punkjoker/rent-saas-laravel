<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'unit_number',
        'unit_type',
        'monthly_rent',
        'deposit_amount',
        'garbage_fee',
        'status',
        'description',
    ];

    protected $casts = [
        'monthly_rent' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'garbage_fee' => 'decimal:2',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
