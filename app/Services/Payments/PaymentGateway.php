<?php

namespace App\Services\Payments;

use App\Models\Payment;

interface PaymentGateway
{
    /**
     * Whether the gateway has the credentials it needs.
     */
    public function isConfigured(): bool;

    /**
     * Create the order and push the payment prompt to the customer's phone.
     *
     * @throws \RuntimeException when the gateway rejects the request
     */
    public function initiate(Payment $payment, string $msisdn): void;

    /**
     * Ask the gateway for the authoritative status of a payment.
     *
     * @return string one of Payment::STATUS_PAID, STATUS_PENDING, STATUS_FAILED
     */
    public function status(Payment $payment): string;
}
