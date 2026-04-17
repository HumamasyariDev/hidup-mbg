<?php

declare(strict_types=1);

namespace App\Domains\Security\Services;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

/**
 * Secure Authentication Service
 *
 * Handles admin login with:
 * - Strict password policy enforcement
 * - IP + email compound rate limiting
 * - Brute force lockout (5 attempts / minute)
 * - Failed attempt logging for intrusion detection
 * - Session regeneration on login
 * - Token ability scoping per role
 */
final class AuthSecurityService
{
    /** Max login attempts per minute per vector */
    private const MAX_ATTEMPTS = 5;

    /** Lockout duration in seconds */
    private const LOCKOUT_SECONDS = 300; // 5 minutes

    /**
     * Password validation rules — enforced at registration and change.
     */
    public static function passwordRules(): array
    {
        return [
            'required',
            'string',
            'confirmed',
            Password::min(12)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised(3), // Check against haveibeenpwned 3 times
        ];
    }

    /**
     * Attempt admin login with comprehensive security checks.
     *
     * @throws ValidationException
     */
    public function attemptLogin(Request $request): array
    {
        $email = strtolower(trim($request->input('email', '')));
        $password = $request->input('password', '');

        // --- Rate limiting: compound by IP + email ---
        $ipKey = 'login_ip:' . $request->ip();
        $emailKey = 'login_email:' . $email;

        if (RateLimiter::tooManyAttempts($ipKey, self::MAX_ATTEMPTS)) {
            $retryAfter = RateLimiter::availableIn($ipKey);
            $this->logFailedAttempt($request, $email, 'rate_limited_ip');

            throw ValidationException::withMessages([
                'email' => ["Terlalu banyak percobaan login. Coba lagi dalam {$retryAfter} detik."],
            ])->status(429);
        }

        if (RateLimiter::tooManyAttempts($emailKey, self::MAX_ATTEMPTS)) {
            $retryAfter = RateLimiter::availableIn($emailKey);
            $this->logFailedAttempt($request, $email, 'rate_limited_email');

            throw ValidationException::withMessages([
                'email' => ["Akun ini dikunci sementara. Coba lagi dalam {$retryAfter} detik."],
            ])->status(429);
        }

        // --- Find admin ---
        $admin = Admin::where('email', $email)->first();

        if (!$admin || !Hash::check($password, $admin->password)) {
            RateLimiter::hit($ipKey, self::LOCKOUT_SECONDS);
            RateLimiter::hit($emailKey, self::LOCKOUT_SECONDS);
            $this->logFailedAttempt($request, $email, 'invalid_credentials');

            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        // --- Account status check ---
        if (!$admin->is_active) {
            $this->logFailedAttempt($request, $email, 'account_inactive');

            throw ValidationException::withMessages([
                'email' => ['Akun Anda telah dinonaktifkan. Hubungi Super Admin.'],
            ]);
        }

        // --- Rehash if cost changed ---
        if (Hash::needsRehash($admin->password)) {
            $admin->update(['password' => Hash::make($password)]);
        }

        // Clear rate limiter on success
        RateLimiter::clear($ipKey);
        RateLimiter::clear($emailKey);

        // Regenerate session to prevent fixation (only if session is available)
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        // Create Sanctum token with role-scoped abilities
        $abilities = $this->getAbilitiesForRole($admin->role);
        $token = $admin->createToken(
            name: "mbg-{$admin->role}-" . now()->timestamp,
            abilities: $abilities,
            expiresAt: now()->addHours(8), // 8-hour token lifetime
        );

        Log::info('Admin login successful', [
            'admin_id' => $admin->id,
            'role'     => $admin->role,
            'ip'       => $request->ip(),
        ]);

        return [
            'admin'   => $admin->only(['id', 'name', 'email', 'role']),
            'token'   => $token->plainTextToken,
            'expires'  => $token->accessToken->expires_at,
            'abilities' => $abilities,
        ];
    }

    /**
     * Logout — revoke current token and log.
     */
    public function logout(Request $request): void
    {
        /** @var Admin $admin */
        $admin = $request->user();

        // Revoke the token used for this request
        $admin->currentAccessToken()->delete();

        // Invalidate session (only if available)
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        Log::info('Admin logout', [
            'admin_id' => $admin->id,
            'ip'       => $request->ip(),
        ]);
    }

    /**
     * Revoke ALL tokens for an admin (emergency lockout).
     */
    public function revokeAllTokens(Admin $admin): int
    {
        $count = $admin->tokens()->count();
        $admin->tokens()->delete();

        Log::warning('All tokens revoked for admin', [
            'admin_id'     => $admin->id,
            'tokens_count' => $count,
        ]);

        return $count;
    }

    /**
     * Map role to Sanctum token abilities (principle of least privilege).
     */
    private function getAbilitiesForRole(string $role): array
    {
        return match ($role) {
            'super_admin' => [
                'dispatch:read', 'dispatch:create',
                'receipt:read', 'receipt:create',
                'feedback:read',
                'audit:read', 'audit:verify',
                'admin:manage', 'school:manage', 'sppg:manage', 'menu:manage',
            ],
            'admin_sppg' => [
                'dispatch:read', 'dispatch:create',
                'receipt:read',
                'feedback:read',
                'menu:manage',
            ],
            'admin_school' => [
                'dispatch:read',
                'receipt:read', 'receipt:create',
                'feedback:read',
            ],
            default => [],
        };
    }

    private function logFailedAttempt(Request $request, string $email, string $reason): void
    {
        Log::channel('security')->warning('Failed login attempt', [
            'email'      => $email,
            'reason'     => $reason,
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp'  => now()->toIso8601String(),
        ]);
    }
}
