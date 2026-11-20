<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'tenant_limit',
        'property_limit',
        'duration_days',
        'currency',
        'sort_order',
        'features',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'tenant_limit' => 'integer',
        'property_limit' => 'integer',
        'duration_days' => 'integer',
        'sort_order' => 'integer',
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }
}
