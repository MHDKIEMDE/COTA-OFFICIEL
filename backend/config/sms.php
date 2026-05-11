<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SMS Provider
    |--------------------------------------------------------------------------
    |
    | provider: 'termii' | 'log'
    | - termii: envoi réel via API Termii
    | - log: n'envoie rien, logge seulement (dev)
    |
    */
    'provider' => env('SMS_PROVIDER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Global options
    |--------------------------------------------------------------------------
    */
    'otp_ttl_minutes' => (int) env('SMS_OTP_TTL_MINUTES', 5),
    'sender_id' => env('SMS_SENDER_ID', 'COTA'),

    /*
    |--------------------------------------------------------------------------
    | Termii
    |--------------------------------------------------------------------------
    | Docs: https://developers.termii.com/
    */
    'termii' => [
        'base_url' => env('TERMII_BASE_URL', 'https://api.ng.termii.com'),
        'api_key' => env('TERMII_API_KEY'),
        'channel' => env('TERMII_CHANNEL', 'generic'),
        'message_template' => env('TERMII_MESSAGE_TEMPLATE', 'Votre code COTA: {code}. Valide {ttl} minutes.'),
    ],
];

