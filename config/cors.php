<?php
// config/cors.php
return [
    'paths'                    => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods'          => ['*'],
    'allowed_origins'          => [
        env('APP_URL', 'http://localhost:8000'),
        env('FRONTEND_URL', 'http://localhost:8000'),
        'https://systemv1.wafragulf.com',
        'https://wafragulf.com',
        'http://localhost',
        'http://localhost:3000',
        'http://127.0.0.1',
        'http://127.0.0.1:8000',
        'null',
    ],
    'allowed_origins_patterns' => [
        '#^https://.*\.wafragulf\.com$#',   // all subdomains
    ],
    'allowed_headers'          => ['*'],
    'exposed_headers'          => [],
    'max_age'                  => 0,
    'supports_credentials'     => true,
];
