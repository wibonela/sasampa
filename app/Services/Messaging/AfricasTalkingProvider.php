<?php

namespace App\Services\Messaging;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AfricasTalkingProvider implements MessagingProviderInterface
{
    private string $apiKey;
    private string $username;
    private string $whatsappProductId;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('messaging.africas_talking.api_key', '');
        $this->username = config('messaging.africas_talking.username', '');
        $this->whatsappProductId = config('messaging.africas_talking.whatsapp_product_id', '');
        $this->baseUrl = config('messaging.africas_talking.sandbox', false)
            ? 'https://api.sandbox.africastalking.com'
            : 'https://api.africastalking.com';
    }

    public function sendWhatsApp(string $phone, string $message): array
    {
        try {
            $response = Http::withHeaders([
                'apiKey' => $this->apiKey,
                'Accept' => 'application/json',
            ])->post("{$this->baseUrl}/version1/messaging", [
                'username' => $this->username,
                'productId' => $this->whatsappProductId,
                'to' => $phone,
                'message' => $message,
                'channel' => 'whatsapp',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $recipients = $data['SMSMessageData']['Recipients'] ?? [];
                $first = $recipients[0] ?? null;

                if ($first && $first['statusCode'] == 101) {
                    return [
                        'success' => true,
                        'provider_message_id' => $first['messageId'] ?? null,
                        'error' => null,
                    ];
                }

                return [
                    'success' => false,
                    'provider_message_id' => null,
                    'error' => $first['status'] ?? 'Unknown error',
                ];
            }

            return [
                'success' => false,
                'provider_message_id' => null,
                'error' => "HTTP {$response->status()}: {$response->body()}",
            ];
        } catch (\Exception $e) {
            Log::error('AfricasTalking WhatsApp error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'provider_message_id' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function sendSms(string $phone, string $message): array
    {
        try {
            $response = Http::withHeaders([
                'apiKey' => $this->apiKey,
                'Accept' => 'application/json',
            ])->post("{$this->baseUrl}/version1/messaging", [
                'username' => $this->username,
                'to' => $phone,
                'message' => $message,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $recipients = $data['SMSMessageData']['Recipients'] ?? [];
                $first = $recipients[0] ?? null;

                if ($first && $first['statusCode'] == 101) {
                    return [
                        'success' => true,
                        'provider_message_id' => $first['messageId'] ?? null,
                        'error' => null,
                    ];
                }

                return [
                    'success' => false,
                    'provider_message_id' => null,
                    'error' => $first['status'] ?? 'Unknown error',
                ];
            }

            return [
                'success' => false,
                'provider_message_id' => null,
                'error' => "HTTP {$response->status()}: {$response->body()}",
            ];
        } catch (\Exception $e) {
            Log::error('AfricasTalking SMS error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'provider_message_id' => null,
                'error' => $e->getMessage(),
            ];
        }
    }
}
