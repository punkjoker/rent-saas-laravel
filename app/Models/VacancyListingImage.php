<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacancyListingImage extends Model
{
    protected $fillable = [
        'image_path',
        'sort_order',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(VacancyListing::class, 'vacancy_listing_id');
    }
}
