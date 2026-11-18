
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(Landlord::class);
    }
}
