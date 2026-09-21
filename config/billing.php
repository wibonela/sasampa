<?php

return [

    // Master switch. While false, every company behaves as it did before billing
    // existed: no feature gating, no plan limits, no expiry.
    'enforced' => env('BILLING_ENFORCED', false),

    // Days of full access given to existing companies when billing is first set up.
    'grace_days' => env('BILLING_GRACE_DAYS', 30),

    // Free trial for newly approved companies.
    'trial_days' => env('BILLING_TRIAL_DAYS', 14),

    // Plan assigned to trials and backfilled companies.
    'default_plan' => 'business',

    // Days after a period ends before the status moves from past_due to expired.
    'past_due_days' => 3,

    // Prepaid discount (percent) by number of months. Mobile money can't auto-renew,
    // so longer prepaid periods are rewarded.
    'discounts' => [1 => 0, 3 => 5, 6 => 10, 12 => 20],

    // Shown on the owner's billing page until online payment is available.
    'payment_instructions' => env('BILLING_PAYMENT_INSTRUCTIONS', 'Contact Sasampa support to renew your plan.'),

    // Plan whose features and limits apply once a period has lapsed.
    'fallback_plan' => 'starter',

    // Selcom Checkout. Leave the key/secret empty to keep online payment switched off
    // (the billing page then shows the manual payment instructions only).
    'selcom' => [
        'base_url' => env('SELCOM_BASE_URL', 'https://apigw.selcommobile.com'),
        'api_key' => env('SELCOM_API_KEY', ''),
        'api_secret' => env('SELCOM_API_SECRET', ''),
        'vendor' => env('SELCOM_VENDOR', ''),
    ],

];
