<?php

namespace App\Services\Messaging;

interface MessagingProviderInterface
{
    /**
     * Send a WhatsApp message.
     *
     * @return array{success: bool, provider_message_id: ?string, error: ?string}
     */
    public function sendWhatsApp(string $phone, string $message): array;

    /**
     * Send an SMS message.
     *
     * @return array{success: bool, provider_message_id: ?string, error: ?string}
     */
    public function sendSms(string $phone, string $message): array;
}
