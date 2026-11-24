<?php

namespace App\Http\Controllers;

use App\Models\PropertyListing;

class PublicPropertyListingController extends Controller
{
    public function show(PropertyListing $listing)
    {
        abort_unless($listing->status === 'approved', 404);
        $listing->load(['images', 'property.units', 'property.landlord']);
        abort_unless(
            $listing->property->is_active &&
            !$listing->property->admin_revoked_at &&
            $listing->property->landlord->is_active &&
            $listing->property->landlord->approval_status === 'approved',
            404
        );

        return view('property-listings.show', compact('listing'));
    }
}
