<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PropertyListing extends Model
{
    protected $fillable = [
        'property_id',
        'slug',
        'title',
        'description',
        'monthly_rent',
        'bedrooms',
        'bathrooms',
        'contact_phone',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'monthly_rent' => 'decimal:2',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PropertyListingImage::class)
            ->orderBy('sort_order');
    }
}
