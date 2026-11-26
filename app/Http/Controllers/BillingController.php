<?php

namespace App\Http\Controllers;

use App\Models\Landlord;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Services\MpesaDaraja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class BillingController extends Controller
{
    public function index()
    {
        $landlord = Auth::user()->landlord;

        if (! $landlord) {
            abort(403, 'Landlord profile not found.');
        }

        $plans = SubscriptionPlan::where('is_active', true)
            ->where('slug', '!=', 'free-trial')
            ->where('price', '>', 0)
            ->orderBy('sort_order')
            ->get();

        $subscription = $landlord->subscriptions()
            ->latest('id')
            ->first();

        $payments = $landlord->subscriptionPayments()
            ->latest()
            ->take(10)
            ->get();

        $access = $landlord->featureAccess()
            ->where('feature', 'accommodations')
            ->first();

        return view('plans', compact(
            'landlord',
            'plans',
            'subscription',
            'payments',
            'access'
        ));
    }

    public function subscribe(Request $request, MpesaDaraja $mpesa)
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:subscription_plans,id'],
            'phone' => ['required', 'regex:/^(?:\+?254|0)?[71]\d{8}$/'],
        ]);
        $plan = SubscriptionPlan::where('is_active', true)
            ->where('slug', '!=', 'free-trial')
            ->where('price', '>', 0)
            ->findOrFail($validated['plan_id']);
        $landlord = $this->landlord();
        $subscription = $landlord->subscriptions()->latest('id')->first();
        $payment = $this->createPayment($landlord, $subscription, $validated['phone'], [
            'purchase' => 'subscription',
            'plan_id' => $plan->id,
            'period' => 'monthly',
        ], (float) $plan->price);

        return $this->startCheckout($payment, $mpesa);
    }

    public function accommodationAccess(Request $request, MpesaDaraja $mpesa)
    {
        $validated = $request->validate([
            'period' => ['required', 'in:monthly,yearly'],
            'phone' => ['required', 'regex:/^(?:\+?254|0)?[71]\d{8}$/'],
        ]);
        $landlord = $this->landlord();
        $subscription = $landlord->subscriptions()->latest('id')->first();
        $amount = $validated['period'] === 'yearly' ? 33600 : 3500;
        $payment = $this->createPayment($landlord, $subscription, $validated['phone'], [
            'purchase' => 'accommodation_addon',
            'period' => $validated['period'],
        ], $amount);

        return $this->startCheckout($payment, $mpesa);
    }

    private function createPayment(
        Landlord $landlord,
        ?Subscription $subscription,
        string $phone,
        array $metadata,
        float $amount
    ): SubscriptionPayment {
        return SubscriptionPayment::create([
            'landlord_id' => $landlord->id,
            'subscription_id' => $subscription?->id,
            'provider' => 'mpesa',
            'phone' => $phone,
            'amount' => $amount,
            'currency' => 'KES',
            'account_reference' => 'PANGO'.Str::upper(Str::random(7)),
            'status' => 'pending',
            'metadata' => $metadata,
        ]);
    }

    private function startCheckout(SubscriptionPayment $payment, MpesaDaraja $mpesa)
    {
        try {
            $request = $mpesa->initiate($payment);
            $payment->update($request);
        } catch (Throwable $exception) {
            $payment->update([
                'status' => 'failed',
                'result_description' => $exception->getMessage(),
            ]);
            Log::warning('M-Pesa subscription checkout failed.', [
                'payment_id' => $payment->id,
                'exception' => $exception->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'phone' => 'M-Pesa checkout could not be started. Check the phone number and payment configuration, then try again.',
            ]);
        }

        return back()->with(
            'success',
            'M-Pesa payment prompt sent. Approve it on your phone; access will activate when payment is confirmed.'
        );
    }

    private function landlord(): Landlord
    {
        $landlord = Auth::user()->landlord;
        abort_unless($landlord, 403, 'Landlord profile not found.');

        return $landlord;
    }
}
