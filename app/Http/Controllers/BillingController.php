<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    public function index()
    {
        $landlord = Auth::user()->landlord;

        if (!$landlord) {
            abort(403, 'Landlord profile not found.');
        }

        $plans = \App\Models\SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $subscription = $landlord->subscriptions()
            ->latest('id')
            ->first();

        $payments = $landlord->subscriptionPayments()
            ->latest()
            ->take(10)
            ->get();

        return view('billing.plans', compact(
            'landlord',
            'plans',
            'subscription',
            'payments'
        ));
    }
}
