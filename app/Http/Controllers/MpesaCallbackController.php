<?php

namespace App\Http\Controllers;

use App\Models\LandlordFeatureAccess;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class MpesaCallbackController extends Controller
{
    public function __invoke(Request $request, string $token): Response
    {
        $configuredToken = (string) config('services.mpesa.callback_token');
        abort_unless(
            $configuredToken !== '' && hash_equals($configuredToken, $token),
            404
        );

        $callback = $request->input('Body.stkCallback');
        $checkoutId = data_get($callback, 'CheckoutRequestID');
        if (! is_array($callback) || ! is_string($checkoutId) || $checkoutId === '') {
            Log::warning('M-Pesa callback omitted its checkout request identifier.');

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        DB::transaction(function () use ($callback, $checkoutId) {
            $payment = SubscriptionPayment::where('checkout_request_id', $checkoutId)
                ->lockForUpdate()
                ->first();

            if (! $payment) {
                Log::warning('Received an M-Pesa callback for an unknown checkout.', [
                    'checkout_request_id' => $checkoutId,
                ]);

                return;
            }

            if ($payment->status === 'paid') {
                return;
            }

            $resultCode = (int) data_get($callback, 'ResultCode', -1);
            $metadataItems = collect(data_get($callback, 'CallbackMetadata.Item', []))
                ->keyBy('Name');
            $paidAmount = (float) data_get($metadataItems, 'Amount.Value', 0);
            $payment->update([
                'result_code' => (string) $resultCode,
                'result_description' => data_get($callback, 'ResultDesc'),
                'callback_payload' => $callback,
                'mpesa_receipt_number' => data_get($metadataItems, 'MpesaReceiptNumber.Value'),
                'phone' => data_get($metadataItems, 'PhoneNumber.Value', $payment->phone),
                'paid_at' => $resultCode === 0 ? now() : null,
                'status' => $resultCode === 0 && round($paidAmount, 2) === round((float) $payment->amount, 2)
                    ? 'paid'
                    : 'failed',
            ]);

            if ($payment->status !== 'paid') {
                if ($resultCode === 0 && round($paidAmount, 2) !== round((float) $payment->amount, 2)) {
                    Log::warning('M-Pesa callback amount did not match the recorded checkout amount.', [
                        'payment_id' => $payment->id,
                        'expected_amount' => (float) $payment->amount,
                        'received_amount' => $paidAmount,
                    ]);
                }

                return;
            }

            $payment->refresh();
            $metadata = $payment->metadata ?? [];
            if (($metadata['purchase'] ?? null) === 'accommodation_addon') {
                $this->activateAccommodationAccess($payment, $metadata);
            } elseif (($metadata['purchase'] ?? null) === 'subscription') {
                $this->activateSubscription($payment, $metadata);
            }
        });

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    private function activateAccommodationAccess(
        SubscriptionPayment $payment,
        array $metadata
    ): void {
        $access = LandlordFeatureAccess::where('landlord_id', $payment->landlord_id)
            ->where('feature', 'accommodations')
            ->lockForUpdate()
            ->first();
        $startsAt = $access && $access->expires_at->isFuture()
            ? $access->expires_at->copy()
            : now();
        $accessStartsAt = $access && $access->expires_at->isFuture()
            ? $access->starts_at
            : now();
        $expiresAt = ($metadata['period'] ?? 'monthly') === 'yearly'
            ? $startsAt->copy()->addYear()
            : $startsAt->copy()->addMonth();

        LandlordFeatureAccess::updateOrCreate(
            [
                'landlord_id' => $payment->landlord_id,
                'feature' => 'accommodations',
            ],
            [
                'starts_at' => $accessStartsAt,
                'expires_at' => $expiresAt,
                'subscription_payment_id' => $payment->id,
            ]
        );
    }

    private function activateSubscription(
        SubscriptionPayment $payment,
        array $metadata
    ): void {
        $plan = SubscriptionPlan::whereKey($metadata['plan_id'] ?? 0)
            ->where('is_active', true)
            ->first();
        if (! $plan) {
            Log::error('Paid subscription checkout references an unavailable plan.', [
                'payment_id' => $payment->id,
                'plan_id' => $metadata['plan_id'] ?? null,
            ]);

            return;
        }

        $subscription = Subscription::where('landlord_id', $payment->landlord_id)
            ->latest('id')
            ->lockForUpdate()
            ->first();
        $startsAt = now();
        $attributes = [
            'plan_id' => $plan->id,
            'selected_plan_id' => $plan->id,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addDays($plan->duration_days),
            'trial_ends_at' => null,
            'status' => 'active',
            'amount_paid' => $payment->amount,
            'payment_reference' => $payment->mpesa_receipt_number,
            'payment_method' => 'mpesa',
            'last_payment_at' => $payment->paid_at,
        ];

        if ($subscription) {
            $subscription->update($attributes);
            $payment->update(['subscription_id' => $subscription->id]);
        } else {
            $subscription = Subscription::create([
                ...$attributes,
                'landlord_id' => $payment->landlord_id,
            ]);
            $payment->update(['subscription_id' => $subscription->id]);
        }
    }
}
