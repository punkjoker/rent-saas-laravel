<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenancyReconciliation extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenancy_id',
        'finalized_at',
        'refund_amount',
        'refunded_at',
        'refund_reference',
        'refund_paid_by',
    ];

    protected $casts = [
        'finalized_at' => 'datetime',
        'refunded_at' => 'datetime',
        'refund_amount' => 'decimal:2',
    ];

    public function tenancy(): BelongsTo
    {
        return $this->belongsTo(Tenancy::class);
    }

    public function refundPaidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refund_paid_by');
    }

    public function damages(): HasMany
    {
        return $this->hasMany(TenancyReconciliationDamage::class);
    }
}
