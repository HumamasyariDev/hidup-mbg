<?php

use App\Domains\Dispatch\Actions\CreateDispatchAction;
use App\Domains\Dispatch\Actions\CreateReceiptAction;
use App\Domains\Feedback\Actions\CreateFeedbackAction;
use App\Domains\Ledger\Services\LedgerService;
use App\Domains\Security\Services\AuthSecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| MBG Platform API Routes — Security Hardened
|--------------------------------------------------------------------------
|
| Middleware stack per route group:
|
| Public:
|   throttle:api
|
| Auth (Login):
|   throttle:login
|
| Admin Protected:
|   auth:sanctum → throttle:reporting → device.fingerprint → geofencing
|
| Anonymous (ZKP):
|   zkp.auth → throttle:feedback → device.fingerprint → geofencing
|
*/

// --- Public / Health ---
Route::middleware('throttle:api')->group(function (): void {
    Route::get('/health', fn () => response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]));
});

// --- Authentication ---
Route::middleware('throttle:login')->group(function (): void {
    Route::post('/auth/login', function (Request $request, AuthSecurityService $auth) {
        $request->validate([
            'email'    => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        return response()->json($auth->attemptLogin($request));
    })->name('auth.login');
});

Route::middleware('auth:sanctum')->post('/auth/logout', function (Request $request, AuthSecurityService $auth) {
    $auth->logout($request);
    return response()->json(['message' => 'Berhasil logout.']);
})->name('auth.logout');

// --- Admin Authenticated Routes ---
Route::middleware([
    'auth:sanctum',
    'throttle:reporting',
    'device.fingerprint',
])->group(function (): void {

    // Current admin info
    Route::get('/me', function (Request $request) {
        return response()->json([
            'data' => $request->user()->only(['id', 'name', 'email', 'role']),
        ]);
    })->name('auth.me');

    // SPPG Provider: Daily Dispatch reporting
    Route::middleware('geofencing:sppg,sppg_provider_id')
        ->post('/dispatches/{sppg_provider_id}', [CreateDispatchAction::class, '__invoke'])
        ->name('dispatches.store');

    // School: Receipt reporting
    Route::middleware('geofencing:school,school_id')
        ->post('/receipts/{school_id}', [CreateReceiptAction::class, '__invoke'])
        ->name('receipts.store');

    // Ledger verification (Super Admin only)
    Route::get('/audit/verify-chain', function (Request $request, LedgerService $ledger) {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['error' => 'forbidden', 'message' => 'Hanya Super Admin yang dapat mengakses.'], 403);
        }
        return response()->json($ledger->verifyChainIntegrity());
    })->name('audit.verify');

    // Audit trail for specific entity
    Route::get('/audit/{entity_type}/{entity_id}', function (Request $request, LedgerService $ledger, string $entity_type, string $entity_id) {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['error' => 'forbidden'], 403);
        }
        $allowedTypes = ['daily_dispatches', 'school_receipts', 'user_feedbacks'];
        if (!in_array($entity_type, $allowedTypes, true)) {
            return response()->json(['error' => 'invalid_entity_type'], 422);
        }
        return response()->json(['data' => $ledger->getEntityAuditTrail($entity_type, $entity_id)]);
    })->name('audit.trail');
});

// --- Anonymous ZKP Routes (User/Student feedback) ---
Route::middleware([
    'zkp.auth',
    'throttle:feedback',
    'device.fingerprint',
    'geofencing:school,school_id',
])->post('/feedback/{school_id}', [CreateFeedbackAction::class, '__invoke'])
  ->name('feedback.store');
