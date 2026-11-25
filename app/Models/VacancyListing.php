<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VacancyListing extends Model
{
    protected $fillable = [
        'source',
        'property_id',
        'unit_id',
        'created_by',
        'title',
        'place_name',
        'address',
        'unit_type',
        'monthly_rent',
        'bedrooms',
        'bathrooms',
        'contact_phone',
        'description',
        'status',
        'published_at',
    ];

    protected $casts = [
        'monthly_rent' => 'decimal:2',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'published_at' => 'datetime',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function images(): HasMany
    {
        return $this->hasMany(VacancyListingImage::class)
            ->orderBy('sort_order');
    }
}
