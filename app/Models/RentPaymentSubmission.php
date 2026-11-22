<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentPaymentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenancy_id',
        'amount',
        'transaction_date',
        'transaction_code',
        'message',
        'status',
        'review_message',
        'reviewed_by',
        'reviewed_at',
        'rent_payment_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function tenancy(): BelongsTo
    {
        return $this->belongsTo(Tenancy::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(RentPayment::class, 'rent_payment_id');
    }
}
