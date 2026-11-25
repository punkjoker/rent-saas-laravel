<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Property extends Model
{
    protected $fillable = [
        'landlord_id',
        'property_name',
        'property_code',
        'property_type',
        'address',
        'town',
        'county',
        'description',
        'is_active',
        'admin_revoked_at',
        'unit_prefix',
        'default_rent',
        'default_deposit',
        'default_garbage_fee',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'admin_revoked_at' => 'datetime',
        'default_rent' => 'decimal:2',
        'default_deposit' => 'decimal:2',
        'default_garbage_fee' => 'decimal:2',
    ];

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(Landlord::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function listing(): HasOne
    {
        return $this->hasOne(PropertyListing::class);
    }

    public function vacancyListings(): HasMany
    {
        return $this->hasMany(VacancyListing::class);
    }
}
