<?php
// config/cors.php
return [
    'paths'                    => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods'          => ['*'],
    'allowed_origins'          => [
        env('FRONTEND_URL', 'http://localhost:8000'),
        'http://localhost',
        'http://localhost:3000',
        'http://127.0.0.1',
        'http://127.0.0.1:8000',
        'null', // for file:// HTML
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers'          => ['*'],
    'exposed_headers'          => [],
    'max_age'                  => 0,
    'supports_credentials'     => true,
];
