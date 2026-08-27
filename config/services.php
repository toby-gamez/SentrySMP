<?php

return [
    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'secret'    => env('PAYPAL_SECRET'),
        'sandbox'   => env('PAYPAL_SANDBOX', false),
    ],
    'stripe' => [
        'secret' => env('STRIPE_SECRET_KEY'),
    ],
    'game_server' => [
        'api_key'  => env('GAME_SERVER_API_KEY', 'changeme'),
        'base_url' => env('GAME_SERVER_BASE_URL', 'http://cz1.sentrysmp.eu:27013'),
    ],
    'admin' => [
        'username' => env('ADMIN_USERNAME', 'admin'),
        'password' => env('ADMIN_PASSWORD', 'changeme'),
    ],
    'discord' => [
        'bot_token'  => env('DISCORD_BOT_TOKEN', ''),
        'channel_id' => env('DISCORD_CHANNEL_ID', ''),
        'guild_id'   => env('DISCORD_GUILD_ID', '1159130895190605854'),
    ],
];
