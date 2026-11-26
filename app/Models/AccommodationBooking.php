<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccommodationBooking extends Model
{
    protected $fillable = [
        'accommodation_id',
        'guest_name',
        'guest_email',
        'guest_phone',
        'check_in',
        'check_out',
        'guests',
        'guest_message',
        'status',
        'responded_at',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'responded_at' => 'datetime',
    ];

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }
}
