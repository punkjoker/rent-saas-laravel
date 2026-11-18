
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'landlord_id',
        'plan_id',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'status',
        'amount_paid',
        'payment_reference',
        'payment_method',
        'last_payment_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'last_payment_at' => 'datetime',
        'amount_paid' => 'decimal:2',
    ];

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(Landlord::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(
            SubscriptionPlan::class,
            'plan_id'
        );
    }
}
