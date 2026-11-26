<?php

namespace App\Services;

use App\Models\SubscriptionPayment;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MpesaDaraja
{
    public function initiate(SubscriptionPayment $payment): array
    {
        $config = config('services.mpesa');
        foreach ([
            'consumer_key',
            'consumer_secret',
            'shortcode',
            'passkey',
            'callback_url',
            'callback_token',
        ] as $key) {
            if (empty($config[$key])) {
                throw new RuntimeException("M-Pesa configuration is missing {$key}.");
            }
        }
        if (strlen($config['callback_token']) < 32 ||
            ! filter_var($config['callback_url'], FILTER_VALIDATE_URL) ||
            parse_url($config['callback_url'], PHP_URL_SCHEME) !== 'https' ||
            ! str_contains($config['callback_url'], $config['callback_token'])) {
            throw new RuntimeException('Configure an HTTPS Daraja callback URL containing a random callback token of at least 32 characters.');
        }

        $baseUrl = $config['environment'] === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
        $accessToken = Http::acceptJson()
            ->withBasicAuth($config['consumer_key'], $config['consumer_secret'])
            ->timeout(15)
            ->get($baseUrl.'/oauth/v1/generate?grant_type=client_credentials')
            ->throw()
            ->json('access_token');

        if (! is_string($accessToken) || $accessToken === '') {
            throw new RuntimeException('Safaricom did not return an M-Pesa access token.');
        }

        $timestamp = now('Africa/Nairobi')->format('YmdHis');
        $shortcode = (string) $config['shortcode'];
        $password = base64_encode($shortcode.$config['passkey'].$timestamp);
        $phone = $this->normalizePhone($payment->phone);

        $response = Http::acceptJson()
            ->withToken($accessToken)
            ->timeout(20)
            ->post($baseUrl.'/mpesa/stkpush/v1/processrequest', [
                'BusinessShortCode' => $shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => (int) round((float) $payment->amount),
                'PartyA' => $phone,
                'PartyB' => $shortcode,
                'PhoneNumber' => $phone,
                'CallBackURL' => $config['callback_url'],
                'AccountReference' => substr($payment->account_reference, 0, 12),
                'TransactionDesc' => 'Pango subscription',
            ])->throw();

        $result = $response->json();
        if ((string) ($result['ResponseCode'] ?? '') !== '0' ||
            empty($result['CheckoutRequestID']) ||
            empty($result['MerchantRequestID'])) {
            throw new RuntimeException(
                $result['ResponseDescription'] ?? 'Safaricom did not accept the STK request.'
            );
        }

        return [
            'checkout_request_id' => $result['CheckoutRequestID'],
            'merchant_request_id' => $result['MerchantRequestID'],
        ];
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '0')) {
            $digits = '254'.substr($digits, 1);
        } elseif (preg_match('/^[71]\d{8}$/', $digits)) {
            $digits = '254'.$digits;
        }

        if (! preg_match('/^254[71]\d{8}$/', $digits)) {
            throw new RuntimeException('Enter a valid Kenyan mobile number, such as 0712345678.');
        }

        return $digits;
    }
}
