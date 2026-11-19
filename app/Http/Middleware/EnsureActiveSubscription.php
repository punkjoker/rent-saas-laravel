<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $landlord = $user->landlord;

        if (!$landlord || !$landlord->is_active) {
            abort(403, 'Landlord account is inactive.');
        }

        $subscription = $landlord->subscriptions()
            ->latest('id')
            ->first();

        if (!$subscription) {
            return redirect()
                ->route('billing.plans')
                ->with('error', 'Please select a subscription plan.');
        }

        $expired = false;

        if ($subscription->status === 'trial') {
            $expired = !$subscription->trial_ends_at ||
                $subscription->trial_ends_at->lte(now());
        }

        if ($subscription->status === 'active') {
            $expired = !$subscription->ends_at ||
                $subscription->ends_at->lte(now());
        }

        if ($expired) {
            $subscription->update([
                'status' => 'expired',
            ]);

            return redirect()
                ->route('billing.plans')
                ->with('error', 'Your subscription has expired. Please renew to continue.');
        }

        if (!in_array($subscription->status, ['trial', 'active'])) {
            return redirect()
                ->route('billing.plans')
                ->with('error', 'Your subscription is not active. Please subscribe to continue.');
        }

        return $next($request);
    }
}
