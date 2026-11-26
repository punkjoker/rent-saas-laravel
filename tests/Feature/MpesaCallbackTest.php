<?php

namespace Tests\Feature;

use App\Models\Landlord;
use App\Models\SubscriptionPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class MpesaCallbackTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.mpesa.callback_token' => str_repeat('x', 40)]);
    }

    public function test_successful_addon_callback_grants_access_for_the_paid_period(): void
    {
        $landlord = $this->createLandlord();
        $payment = $this->createPayment($landlord, 3500, 'monthly');

        $this->postJson(route('mpesa.callback', config('services.mpesa.callback_token')), [
            'Body' => ['stkCallback' => [
                'CheckoutRequestID' => $payment->checkout_request_id,
                'ResultCode' => 0,
                'ResultDesc' => 'The service request is processed successfully.',
                'CallbackMetadata' => ['Item' => [
                    ['Name' => 'Amount', 'Value' => 3500],
                    ['Name' => 'MpesaReceiptNumber', 'Value' => 'TEST123456'],
                    ['Name' => 'PhoneNumber', 'Value' => 254712345678],
                ]],
            ]],
        ])->assertOk();

        $this->assertDatabaseHas('subscription_payments', [
            'id' => $payment->id,
            'status' => 'paid',
            'mpesa_receipt_number' => 'TEST123456',
        ]);
        $this->assertDatabaseHas('landlord_feature_access', [
            'landlord_id' => $landlord->id,
            'feature' => 'accommodations',
        ]);
        $this->assertTrue($landlord->featureAccess()->first()->expires_at->isFuture());
    }

    public function test_callback_with_a_mismatched_amount_does_not_grant_access(): void
    {
        $landlord = $this->createLandlord();
        $payment = $this->createPayment($landlord, 3500, 'monthly');

        $this->postJson(route('mpesa.callback', config('services.mpesa.callback_token')), [
            'Body' => ['stkCallback' => [
                'CheckoutRequestID' => $payment->checkout_request_id,
                'ResultCode' => 0,
                'ResultDesc' => 'The service request is processed successfully.',
                'CallbackMetadata' => ['Item' => [
                    ['Name' => 'Amount', 'Value' => 10],
                    ['Name' => 'MpesaReceiptNumber', 'Value' => 'TEST654321'],
                ]],
            ]],
        ])->assertOk();

        $this->assertDatabaseHas('subscription_payments', [
            'id' => $payment->id,
            'status' => 'failed',
        ]);
        $this->assertDatabaseMissing('landlord_feature_access', [
            'landlord_id' => $landlord->id,
            'feature' => 'accommodations',
        ]);
    }

    private function createLandlord(): Landlord
    {
        $user = User::create([
            'name' => 'M-Pesa landlord',
            'email' => Str::uuid().'@example.test',
            'password' => 'password',
            'role' => 'landlord',
        ]);

        return Landlord::create([
            'user_id' => $user->id,
            'business_name' => 'M-Pesa test account',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
    }

    private function createPayment(Landlord $landlord, int $amount, string $period): SubscriptionPayment
    {
        return SubscriptionPayment::create([
            'landlord_id' => $landlord->id,
            'provider' => 'mpesa',
            'phone' => '0712345678',
            'amount' => $amount,
            'currency' => 'KES',
            'account_reference' => 'PANGOTEST',
            'checkout_request_id' => Str::uuid()->toString(),
            'status' => 'pending',
            'metadata' => [
                'purchase' => 'accommodation_addon',
                'period' => $period,
            ],
        ]);
    }
}
