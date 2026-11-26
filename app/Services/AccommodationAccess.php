<?php

namespace App\Services;

use App\Models\Landlord;

class AccommodationAccess
{
    public function enabled(Landlord $landlord): bool
    {
        $subscription = $landlord->subscriptions()
            ->with(['plan', 'selectedPlan'])
            ->latest('id')
            ->first();

        if ($subscription) {
            $active = ($subscription->status === 'trial' &&
                    $subscription->trial_ends_at?->isFuture()) ||
                ($subscription->status === 'active' &&
                    $subscription->ends_at?->isFuture());

            if ($active && (
                $subscription->plan?->slug === 'accommodations' ||
                ($subscription->status === 'trial' &&
                    $subscription->selectedPlan?->slug === 'accommodations')
            )) {
                return true;
            }
        }

        return $landlord->featureAccess()
            ->where('feature', 'accommodations')
            ->where('starts_at', '<=', now())
            ->where('expires_at', '>', now())
            ->exists();
    }
}
