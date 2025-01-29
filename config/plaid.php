<?php

return [
    'plaid_client_id' => env('PLAID_CLIENT_ID', ''),
    'plaid_secret' => env('PLAID_SECRET', ''),
    'plaid_env' => env('PLAID_ENV', 'sandbox'),
    'plaid_products' => env('PLAID_PRODUCTS', 'transactions'),
    'plaid_redirect_uri' => env('PLAID_REDIRECT_URI', ''),
    'plaid_env_url' => [
        'production' => 'https://production.plaid.com',
        'sandbox' => 'https://sandbox.plaid.com',
        'development' => 'https://development.plaid.com'
    ]
];