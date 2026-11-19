<?php

namespace App\Services;

use App\Models\Landlord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class TenantLimitService
{
    public function assertCanAddTenant(Landlord $landlord): void
    {
        $subscription = $landlord->subscriptions()
            ->latest('id')
            ->first();

        if (!$subscription ||
            !in_array($subscription->status, ['trial', 'active'])) {
            throw ValidationException::withMessages([
                'tenant' => 'Your subscription is not active. Please renew.'
            ]);
        }

        if ($subscription->status === 'trial' &&
            (!$subscription->trial_ends_at ||
             $subscription->trial_ends_at->lte(now()))) {
            throw ValidationException::withMessages([
                'tenant' => 'Your free trial has expired.'
            ]);
        }

        if ($subscription->status === 'active' &&
            (!$subscription->ends_at ||
             $subscription->ends_at->lte(now()))) {
            throw ValidationException::withMessages([
                'tenant' => 'Your subscription has expired.'
            ]);
        }

        $plan = $subscription->plan;

        if (!$plan) {
            throw new RuntimeException(
                'The subscription does not have an assigned plan.'
            );
        }

        // Null means unlimited tenants.
        if ($plan->tenant_limit === null) {
            return;
        }

        if (!Schema::hasTable('tenants')) {
            throw new RuntimeException(
                'Tenant table has not yet been created.'
            );
        }

        $tenantCount = DB::table('tenants')
            ->where('landlord_id', $landlord->id)
            ->where('status', 'active')
            ->count();

        if ($tenantCount >= $plan->tenant_limit) {
            throw ValidationException::withMessages([
                'tenant' => "Your {$plan->name} package allows a maximum of {$plan->tenant_limit} active tenants. Upgrade your subscription to add more."
            ]);
        }
    }
}
