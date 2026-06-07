<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
    ],

    'facebook' => [
        'client_id'     => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect'      => env('FACEBOOK_REDIRECT_URI', '/auth/facebook/callback'),
    ],

    'paydunya' => [
        'master_key' => env('PAYDUNYA_MASTER_KEY'),
        'private_key' => env('PAYDUNYA_PRIVATE_KEY'),
        'token' => env('PAYDUNYA_TOKEN'),
        'mode' => env('PAYDUNYA_MODE', 'test'), // 'test' ou 'live'
    ],

    'firebase' => [
        'project_id'       => env('FIREBASE_PROJECT_ID'),
        'credentials_path' => env('FIREBASE_CREDENTIALS_PATH'),
    ],

    'openweathermap' => [
        'key' => env('OPENWEATHERMAP_KEY'),
    ],

    'sportradar' => [
        'api_key' => env('SPORTRADAR_API_KEY'),
    ],

    'football_data_org' => [
        'key' => env('FOOTBALL_DATA_ORG_KEY'),
    ],

    'the_odds_api' => [
        'key' => env('THE_ODDS_API_KEY'),
    ],

    // RapidAPI — clé commune + clés par service
    'rapidapi' => [
        'key'             => env('RAPIDAPI_KEY', ''),
        'livestream_key'  => env('RAPIDAPI_LIVESTREAM_KEY', ''),
        'football_data_key' => env('RAPIDAPI_FOOTBALL_DATA_KEY', ''),
        'flashscore_key'  => env('RAPIDAPI_FLASHSCORE_KEY', ''),
        'prediction_key'  => env('RAPIDAPI_PREDICTION_KEY', ''),
        'odds_1xbet_key'  => env('RAPIDAPI_1XBET_ODDS_KEY', ''),
        'videos_key'      => env('RAPIDAPI_VIDEOS_KEY', ''),
    ],

    'gnews' => [
        'key' => env('GNEWS_API_KEY'),
    ],

    // SportAPI7 (SofaScore-based) — matchs football, cotes, forme, H2H, classements
    'sportapi7' => [
        'key' => env('SPORTAPI7_KEY', env('RAPIDAPI_KEY', '')),
    ],

    // Bet365Data — cotes tennis et basketball en temps réel
    'bet365data' => [
        'key' => env('BET365DATA_KEY', env('RAPIDAPI_KEY', '')),
    ],

    'thesportsdb' => [
        'key' => env('THESPORTSDB_KEY', '3'),
    ],

    'anthropic' => [
        'key'   => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-haiku-4-5-20251001'),
    ],

    // Couche LLM pour l'analyse IA (§9 CDC V2)
    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN', ''),
    ],

    // provider: 'anthropic' | 'openai' | 'none' (template fallback)
    'llm' => [
        'provider'        => env('LLM_PROVIDER', 'none'),
        'anthropic_key'   => env('ANTHROPIC_API_KEY'),
        'anthropic_model' => env('ANTHROPIC_MODEL', 'claude-haiku-4-5-20251001'),
        'openai_key'      => env('OPENAI_API_KEY'),
        'openai_model'    => env('OPENAI_MODEL', 'gpt-4o-mini'),
    ],

];
