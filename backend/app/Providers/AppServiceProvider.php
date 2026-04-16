<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Configure granular rate limiters for each endpoint category.
     */
    private function configureRateLimiting(): void
    {
        // Login: strict — 5/min per IP + 5/min per email
        RateLimiter::for('login', function (Request $request) {
            return [
                Limit::perMinute(5)->by($request->ip())->response(fn () => response()->json([
                    'error' => 'too_many_login_attempts',
                    'message' => 'Terlalu banyak percobaan login dari IP ini.',
                ], 429)),
                Limit::perMinute(5)->by(strtolower((string) $request->input('email')))->response(fn () => response()->json([
                    'error' => 'account_temporarily_locked',
                    'message' => 'Akun ini dikunci sementara karena percobaan login berulang.',
                ], 429)),
            ];
        });

        // Dispatch/Receipt reporting: 30/min per admin (prevents automated spam)
        RateLimiter::for('reporting', function (Request $request) {
            return Limit::perMinute(30)
                ->by($request->user()?->id ?: $request->ip())
                ->response(fn () => response()->json([
                    'error' => 'reporting_rate_limited',
                    'message' => 'Batas pelaporan per menit tercapai.',
                ], 429));
        });

        // Feedback: 10/min per ZKP identity (anti-spam)
        RateLimiter::for('feedback', function (Request $request) {
            $identity = $request->input('zkp_identity_hash', $request->ip());
            return Limit::perMinute(10)
                ->by($identity)
                ->response(fn () => response()->json([
                    'error' => 'feedback_rate_limited',
                    'message' => 'Terlalu banyak feedback. Coba lagi nanti.',
                ], 429));
        });

        // General API: 60/min per IP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip());
        });

        // File upload: 10/min per user (prevent upload flooding)
        RateLimiter::for('upload', function (Request $request) {
            return Limit::perMinute(10)
                ->by($request->user()?->id ?: $request->ip())
                ->response(fn () => response()->json([
                    'error' => 'upload_rate_limited',
                    'message' => 'Terlalu banyak upload. Coba lagi nanti.',
                ], 429));
        });
    }
}
