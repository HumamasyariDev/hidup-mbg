<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * ZKP Authentication Middleware
 *
 * Intercepts requests from anonymous users and validates their Zero-Knowledge Proof token.
 * The frontend sends a ZKP proof + DID identifier; this middleware:
 *   1. Extracts the proof from the Authorization header (Bearer zkp:<proof>)
 *   2. Verifies the proof structure and signature
 *   3. Derives a deterministic identity hash (SHA-256 of the DID) for anti-spam
 *   4. Checks rate limiting per identity hash
 *   5. Attaches the validated identity hash to the request
 *
 * This is a FRAMEWORK — the actual ZKP verification logic depends on your
 * chosen ZKP library (e.g., snarkjs, circom, zk-STARK). Replace verifyZkpProof().
 */
final class ZkpAuthMiddleware
{
    private const MAX_REQUESTS_PER_IDENTITY_PER_HOUR = 20;

    private const TOKEN_PREFIX = 'zkp:';

    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization', '');

        // Extract ZKP token: "Bearer zkp:<proof_payload>"
        if (! str_starts_with($authHeader, 'Bearer '.self::TOKEN_PREFIX)) {
            return response()->json([
                'error' => 'zkp_token_required',
                'message' => 'Token ZKP anonim diperlukan. Format: Bearer zkp:<proof>',
            ], 401);
        }

        $proofPayload = substr($authHeader, strlen('Bearer '.self::TOKEN_PREFIX));

        if (empty($proofPayload)) {
            return response()->json([
                'error' => 'zkp_token_empty',
                'message' => 'Payload ZKP tidak boleh kosong.',
            ], 401);
        }

        // Decode the proof payload (expected: base64-encoded JSON)
        $decoded = json_decode(base64_decode($proofPayload, true) ?: '', true);

        if (! is_array($decoded) || ! isset($decoded['did'], $decoded['proof'], $decoded['timestamp'])) {
            return response()->json([
                'error' => 'zkp_token_malformed',
                'message' => 'Format token ZKP tidak valid. Diperlukan: did, proof, timestamp.',
            ], 401);
        }

        // Verify timestamp freshness (prevent replay attacks — 5 minute window)
        $tokenAge = abs(time() - (int) $decoded['timestamp']);
        if ($tokenAge > 300) {
            return response()->json([
                'error' => 'zkp_token_expired',
                'message' => 'Token ZKP sudah kedaluwarsa. Silakan generate ulang.',
            ], 401);
        }

        // Derive deterministic identity hash from DID
        $identityHash = hash('sha256', $decoded['did']);

        // Verify the ZKP proof itself
        if (! $this->verifyZkpProof($decoded['did'], $decoded['proof'])) {
            return response()->json([
                'error' => 'zkp_proof_invalid',
                'message' => 'Bukti ZKP tidak valid atau gagal diverifikasi.',
            ], 403);
        }

        // Rate limit per identity hash
        $cacheKey = "zkp_rate:{$identityHash}";
        $attempts = (int) Cache::get($cacheKey, 0);

        if ($attempts >= self::MAX_REQUESTS_PER_IDENTITY_PER_HOUR) {
            return response()->json([
                'error' => 'zkp_rate_limited',
                'message' => 'Terlalu banyak permintaan dari identitas ini. Coba lagi nanti.',
            ], 429);
        }

        Cache::put($cacheKey, $attempts + 1, now()->addHour());

        // Attach validated ZKP data to request for downstream controllers
        $request->merge([
            'zkp_identity_hash' => $identityHash,
            'zkp_proof' => $decoded['proof'],
            'zkp_did' => $decoded['did'],
        ]);

        return $next($request);
    }

    /**
     * Verify the ZKP proof.
     *
     * IMPORTANT: This is a placeholder. Replace with your actual ZKP verification logic.
     * Options include:
     *   - snarkjs/circom SNARK verification
     *   - Semaphore protocol group membership proof
     *   - Custom zk-STARK verifier
     *   - External ZKP verification microservice call
     *
     * The proof should demonstrate that the user possesses a valid DID
     * WITHOUT revealing their actual identity.
     */
    private function verifyZkpProof(string $did, string $proof): bool
    {
        // -----------------------------------------------------------
        // REPLACE THIS with actual ZKP verification.
        // Example pseudocode for Semaphore-style proof:
        //
        // $verifier = app(ZkpVerifierInterface::class);
        // return $verifier->verify(
        //     signal: $did,
        //     proof: $proof,
        //     merkleRoot: $this->getGroupMerkleRoot(),
        //     externalNullifier: config('zkp.external_nullifier'),
        // );
        // -----------------------------------------------------------

        // Minimal structural validation as fallback
        if (strlen($proof) < 32) {
            return false;
        }

        // In development, accept all structurally valid proofs
        if (app()->environment('local', 'testing')) {
            return true;
        }

        // In production, this MUST be replaced with real verification
        // Fail-closed: reject if no real verifier is configured
        return false;
    }
}
