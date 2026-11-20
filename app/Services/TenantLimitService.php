<?php

namespace App\Services;

use App\Models\Landlord;
use App\Models\SubscriptionPlan;
use App\Models\Tenancy;
use Illuminate\Validation\ValidationException;

class TenantLimitService
{
    public function assertCanAddTenant(
        Landlord $landlord,
        ?int $tenantId = null
    ): void
    {
        $subscription = $landlord->subscriptions()
            ->with('plan')
            ->whereIn('status', ['trial', 'active'])
            ->latest('id')
            ->first();

        if (!$subscription) {
            throw ValidationException::withMessages([
                'tenant_id' => 'You do not have an active subscription or trial. Please review your subscription.',
            ]);
        }

        if (
            $subscription->status === 'trial'
            && $subscription->trial_ends_at
            && $subscription->trial_ends_at->isPast()
        ) {
            throw ValidationException::withMessages([
                'tenant_id' => 'Your free trial has expired. Please choose a subscription plan.',
            ]);
        }

        if (
            $subscription->status === 'active'
            && $subscription->ends_at
            && $subscription->ends_at->isPast()
        ) {
            throw ValidationException::withMessages([
                'tenant_id' => 'Your subscription has expired. Please renew it to create a tenancy.',
            ]);
        }

        $plan = $subscription->plan;

        if (!$plan && $subscription->status === 'trial') {
            $plan = SubscriptionPlan::where('slug', 'free-trial')->first();
        }

        if (!$plan) {
            throw ValidationException::withMessages([
                'tenant_id' => 'Your subscription has no assigned plan. Please review your subscription.',
            ]);
        }

        if ($plan->tenant_limit === null) {
            return;
        }

        if (
            $tenantId !== null
            && Tenancy::where('landlord_id', $landlord->id)
                ->where('tenant_id', $tenantId)
                ->whereIn('status', ['active', 'upcoming'])
                ->exists()
        ) {
            return;
        }

        $tenantCount = Tenancy::where('landlord_id', $landlord->id)
            ->whereIn('status', ['active', 'upcoming'])
            ->distinct('tenant_id')
            ->count('tenant_id');

        if ($tenantCount >= $plan->tenant_limit) {
            throw ValidationException::withMessages([
                'tenant_id' => "Your subscription allows {$plan->tenant_limit} active tenants. Upgrade your plan to allocate another tenant.",
            ]);
        }
    }
}
