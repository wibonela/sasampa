<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Selcom Checkout: create-order-minimal, then wallet-payment to push a USSD prompt.
 * Requests are signed HS256 as in Selcom's reference clients.
 */
class SelcomGateway implements PaymentGateway
{
    public function isConfigured(): bool
    {
        return config('billing.selcom.api_key') !== ''
            && config('billing.selcom.api_secret') !== ''
            && config('billing.selcom.vendor') !== '';
    }

    public function initiate(Payment $payment, string $msisdn): void
    {
        $company = $payment->company;
        $owner = $company->owner;

        $this->post('/v1/checkout/create-order-minimal', [
            'vendor' => config('billing.selcom.vendor'),
            'order_id' => $payment->gateway_ref,
            'buyer_email' => $owner?->email ?? $company->email,
            'buyer_name' => $owner?->name ?? $company->name,
            'buyer_phone' => $msisdn,
            'amount' => $payment->amount_tzs,
            'currency' => 'TZS',
            'buyer_remarks' => 'Sasampa subscription',
            'merchant_remarks' => 'Company #' . $company->id,
            'no_of_items' => 1,
            'webhook' => base64_encode(route('webhooks.selcom')),
        ]);

        $this->post('/v1/checkout/wallet-payment', [
            'transid' => (string) Str::uuid(),
            'order_id' => $payment->gateway_ref,
            'msisdn' => $msisdn,
        ]);
    }

    public function status(Payment $payment): string
    {
        $response = $this->send('GET', '/v1/checkout/order-status', ['order_id' => $payment->gateway_ref]);
        $order = $response['data'][0] ?? null;

        if (($response['result'] ?? null) !== 'SUCCESS' || !$order) {
            // Unknown order or lookup error: leave it pending rather than guess.
            return Payment::STATUS_PENDING;
        }

        return match (strtoupper($order['payment_status'] ?? '')) {
            'COMPLETED' => Payment::STATUS_PAID,
            'CANCELLED', 'REJECTED', 'USERCANCELLED', 'EXPIRED', 'FAILED' => Payment::STATUS_FAILED,
            default => Payment::STATUS_PENDING,
        };
    }

    /**
     * Verify the signature headers on an incoming webhook.
     */
    public function signatureIsValid(array $headers, array $payload): bool
    {
        $fields = array_filter(explode(',', $headers['signed-fields'] ?? ''));
        $timestamp = $headers['timestamp'] ?? '';
        $digest = $headers['digest'] ?? '';

        if (!$fields || !$timestamp || !$digest) {
            return false;
        }

        return hash_equals($this->digest($timestamp, $fields, $payload), $digest);
    }

    private function post(string $path, array $data): array
    {
        return $this->send('POST', $path, $data);
    }

    private function send(string $method, string $path, array $data): array
    {
        $timestamp = now()->format('Y-m-d\TH:i:sO');
        $fields = array_keys($data);

        $request = Http::baseUrl(config('billing.selcom.base_url'))
            ->acceptJson()
            ->timeout(20)
            ->withHeaders([
                'Authorization' => 'SELCOM ' . base64_encode(config('billing.selcom.api_key')),
                'Digest-Method' => 'HS256',
                'Digest' => $this->digest($timestamp, $fields, $data),
                'Timestamp' => $timestamp,
                'Signed-Fields' => implode(',', $fields),
            ]);

        $response = $method === 'GET' ? $request->get($path, $data) : $request->asJson()->post($path, $data);
        $body = $response->json() ?? [];

        if ($method === 'POST' && (!$response->successful() || ($body['result'] ?? '') !== 'SUCCESS')) {
            throw new \RuntimeException($body['message'] ?? 'Payment provider rejected the request.');
        }

        return $body;
    }

    private function digest(string $timestamp, array $fields, array $data): string
    {
        $signing = 'timestamp=' . $timestamp;
        foreach ($fields as $field) {
            $signing .= '&' . $field . '=' . ($data[$field] ?? '');
        }

        return base64_encode(hash_hmac('sha256', $signing, config('billing.selcom.api_secret'), true));
    }
}
