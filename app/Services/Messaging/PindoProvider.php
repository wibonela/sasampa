<?php

namespace App\Services\Messaging;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PindoProvider implements MessagingProviderInterface
{
    private string $apiToken;
    private string $senderId;
    private string $baseUrl = 'https://api.pindo.io/v1';

    public function __construct()
    {
        $this->apiToken = config('messaging.pindo.api_token', '');
        $this->senderId = config('messaging.pindo.sender_id', 'Sasampa');
    }

    public function sendWhatsApp(string $phone, string $message): array
    {
        try {
            $response = Http::withToken($this->apiToken)
                ->post("{$this->baseUrl}/whatsapp/", [
                    'to' => $phone,
                    'body' => $message,
                    'sender' => $this->senderId,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'provider_message_id' => $data['message_id'] ?? $data['id'] ?? null,
                    'error' => null,
                ];
            }

            return [
                'success' => false,
                'provider_message_id' => null,
                'error' => "HTTP {$response->status()}: {$response->body()}",
            ];
        } catch (\Exception $e) {
            Log::error('Pindo WhatsApp error', ['error' => $e->getMessage()]);
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
            $response = Http::withToken($this->apiToken)
                ->post("{$this->baseUrl}/sms/", [
                    'to' => $phone,
                    'text' => $message,
                    'sender' => $this->senderId,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'provider_message_id' => $data['message_id'] ?? $data['id'] ?? null,
                    'error' => null,
                ];
            }

            return [
                'success' => false,
                'provider_message_id' => null,
                'error' => "HTTP {$response->status()}: {$response->body()}",
            ];
        } catch (\Exception $e) {
            Log::error('Pindo SMS error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'provider_message_id' => null,
                'error' => $e->getMessage(),
            ];
        }
    }
}
