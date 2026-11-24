<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionExtension extends Model
{
    protected $fillable = [
        'landlord_id',
        'subscription_id',
        'admin_id',
        'days',
        'previous_ends_at',
        'new_ends_at',
        'reason',
    ];

    protected $casts = [
        'days' => 'integer',
        'previous_ends_at' => 'datetime',
        'new_ends_at' => 'datetime',
    ];

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(Landlord::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
