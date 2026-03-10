<?php

return [

    'provider' => env('WHATSAPP_PROVIDER', 'stub'),

    'africas_talking' => [
        'api_key' => env('AT_API_KEY', ''),
        'username' => env('AT_USERNAME', ''),
        'whatsapp_product_id' => env('AT_WHATSAPP_PRODUCT_ID', ''),
        'sandbox' => env('AT_SANDBOX', false),
    ],

    'pindo' => [
        'api_token' => env('PINDO_API_TOKEN', ''),
        'sender_id' => env('PINDO_SENDER_ID', 'Sasampa'),
    ],

];
