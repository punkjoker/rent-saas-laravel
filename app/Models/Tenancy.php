<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenancy extends Model
{
    use HasFactory;

    protected $fillable = [
        'landlord_id',
        'tenant_id',
        'property_id',
        'unit_id',
        'monthly_rent',
        'deposit_amount',
        'garbage_fee',
        'start_date',
        'lease_end_date',
        'rent_due_day',
        'status',
        'ended_at',
        'notes',
        'payment_share_token_hash',
    ];

    protected $casts = [
        'monthly_rent' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'garbage_fee' => 'decimal:2',
        'start_date' => 'date',
        'lease_end_date' => 'date',
        'ended_at' => 'date',
    ];

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(Landlord::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function rentCharges(): HasMany
    {
        return $this->hasMany(RentCharge::class);
    }

    public function rentPayments(): HasMany
    {
        return $this->hasMany(RentPayment::class);
    }

    public function rentPaymentSubmissions(): HasMany
    {
        return $this->hasMany(RentPaymentSubmission::class);
    }

    public function depositPayments(): HasMany
    {
        return $this->hasMany(DepositPayment::class);
    }
}