<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RentPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenancy_id',
        'amount',
        'paid_at',
        'transaction_code',
        'payment_method',
        'message',
        'source',
        'recorded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function tenancy(): BelongsTo
    {
        return $this->belongsTo(Tenancy::class);
    }

    public function submission(): HasOne
    {
        return $this->hasOne(RentPaymentSubmission::class);
    }
}
