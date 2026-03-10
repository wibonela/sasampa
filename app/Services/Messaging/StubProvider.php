<?php

namespace App\Services\Messaging;

use Illuminate\Support\Facades\Log;

class StubProvider implements MessagingProviderInterface
{
    public function sendWhatsApp(string $phone, string $message): array
    {
        Log::channel('messaging')->info('STUB WhatsApp', [
            'phone' => $phone,
            'message' => $message,
        ]);

        return [
            'success' => true,
            'provider_message_id' => 'stub_' . uniqid(),
            'error' => null,
        ];
    }

    public function sendSms(string $phone, string $message): array
    {
        Log::channel('messaging')->info('STUB SMS', [
            'phone' => $phone,
            'message' => $message,
        ]);

        return [
            'success' => true,
            'provider_message_id' => 'stub_' . uniqid(),
            'error' => null,
        ];
    }
}
