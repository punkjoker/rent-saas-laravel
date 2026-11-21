<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenancy_id',
        'month_year',
        'rent_amount',
        'garbage_fee',
        'water_bill',
        'total_due',
    ];

    protected $casts = [
        'rent_amount' => 'decimal:2',
        'garbage_fee' => 'decimal:2',
        'water_bill' => 'decimal:2',
        'total_due' => 'decimal:2',
    ];

    public function tenancy(): BelongsTo
    {
        return $this->belongsTo(Tenancy::class);
    }
}