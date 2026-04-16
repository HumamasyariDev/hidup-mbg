<?php

/**
 * CORS Configuration — Hardened
 *
 * Restricts API access to explicitly allowed frontend origins.
 * NEVER use wildcard ('*') for authenticated endpoints.
 */
return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    /**
     * Allowed origins — set via environment variable.
     * In production: 'https://app.mbg-platform.id'
     * In local dev:  'http://localhost:3000,http://localhost:5173'
     */
    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000,http://localhost:5173')),

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'Authorization',
        'Accept',
        'X-Requested-With',
        'X-XSRF-TOKEN',
        'X-CSRF-TOKEN',
        'X-Geo-Latitude',
        'X-Geo-Longitude',
        'X-Geo-Accuracy',
        'X-Geo-Altitude',
        'X-Geo-Mock',
        'X-Geo-Timestamp',
        'X-Device-Fingerprint',
    ],

    'exposed_headers' => ['X-RateLimit-Limit', 'X-RateLimit-Remaining', 'Retry-After'],

    'max_age' => 3600, // Preflight cache: 1 hour

    'supports_credentials' => true,
];
