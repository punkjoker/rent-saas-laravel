<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPayment extends Model
{
    protected $fillable = [
        'landlord_id',
        'subscription_id',
        'provider',
        'phone',
        'amount',
        'currency',
        'account_reference',
        'checkout_request_id',
        'merchant_request_id',
        'mpesa_receipt_number',
        'status',
        'result_code',
        'result_description',
        'callback_payload',
        'metadata',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'callback_payload' => 'array',
        'metadata' => 'array',
        'paid_at' => 'datetime',
    ];

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(Landlord::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
