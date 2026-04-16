<?php

declare(strict_types=1);

use App\Http\Middleware\SecurityHeadersMiddleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

// This test requires full app boot for app()->isProduction()
test('SecurityHeadersMiddleware adds security headers', function (): void {
    $response = $this->get('/api/health');

    expect($response->headers->get('X-Content-Type-Options'))->toBe('nosniff');
    expect($response->headers->get('X-Frame-Options'))->toBe('DENY');
    expect($response->headers->get('X-XSS-Protection'))->toBe('1; mode=block');
    expect($response->headers->get('Referrer-Policy'))->toBe('strict-origin-when-cross-origin');
    expect($response->headers->has('Content-Security-Policy'))->toBeTrue();
    expect($response->headers->has('Permissions-Policy'))->toBeTrue();
    // API responses should have no-cache
    expect($response->headers->get('Cache-Control'))->toContain('no-store');
});
