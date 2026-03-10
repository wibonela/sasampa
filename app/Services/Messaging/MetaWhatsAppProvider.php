<?php

namespace App\Services\Messaging;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Meta WhatsApp Cloud API provider.
 *
 * Uses the official Meta Graph API directly (no third-party BSP needed).
 * Create your app at https://developers.facebook.com/apps/creation/
 * Select "Connect with customers through WhatsApp".
 *
 * Required .env vars:
 *   META_WHATSAPP_TOKEN       - Permanent System User access token
 *   META_WHATSAPP_PHONE_ID    - Phone Number ID from WhatsApp > API Setup
 *   META_WHATSAPP_BUSINESS_ID - WhatsApp Business Account ID (optional, for webhooks)
 */
class MetaWhatsAppProvider implements MessagingProviderInterface
{
    private string $token;
    private string $phoneNumberId;
    private string $apiVersion;

    public function __construct()
    {
        $this->token = config('messaging.meta.access_token', '');
        $this->phoneNumberId = config('messaging.meta.phone_number_id', '');
        $this->apiVersion = config('messaging.meta.api_version', 'v22.0');
    }

    public function sendWhatsApp(string $phone, string $message): array
    {
        try {
            // Normalize phone: ensure it starts with country code, no spaces/dashes
            $phone = preg_replace('/[^0-9+]/', '', $phone);
            if (!str_starts_with($phone, '+')) {
                $phone = '+' . $phone;
            }
            // Remove the + for Meta API (they want just digits)
            $phoneDigits = ltrim($phone, '+');

            $url = "https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages";

            $response = Http::withToken($this->token)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $phoneDigits,
                    'type' => 'text',
                    'text' => [
                        'preview_url' => false,
                        'body' => $message,
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $messageId = $data['messages'][0]['id'] ?? null;

                return [
                    'success' => true,
                    'provider_message_id' => $messageId,
                    'error' => null,
                ];
            }

            // Parse Meta error response
            $errorData = $response->json();
            $errorMsg = $errorData['error']['message']
                ?? $errorData['error']['error_user_msg']
                ?? "HTTP {$response->status()}";
            $errorCode = $errorData['error']['code'] ?? null;

            Log::warning('Meta WhatsApp send failed', [
                'phone' => $phoneDigits,
                'status' => $response->status(),
                'error_code' => $errorCode,
                'error' => $errorMsg,
            ]);

            return [
                'success' => false,
                'provider_message_id' => null,
                'error' => $errorMsg,
            ];
        } catch (\Exception $e) {
            Log::error('Meta WhatsApp exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'provider_message_id' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * SMS is not supported by Meta WhatsApp Cloud API.
     * This will always return failure — the job's SMS fallback
     * should use a separate SMS provider if configured.
     */
    public function sendSms(string $phone, string $message): array
    {
        Log::info('Meta provider does not support SMS', ['phone' => $phone]);
        return [
            'success' => false,
            'provider_message_id' => null,
            'error' => 'Meta WhatsApp Cloud API does not support SMS. Configure Africa\'s Talking or Pindo for SMS fallback.',
        ];
    }
}
