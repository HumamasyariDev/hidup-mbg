<?php

declare(strict_types=1);

namespace App\Domains\Ledger\Services;

use App\Models\AuditLedger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * LedgerService — Immutable Audit Trail
 *
 * Creates blockchain-like chained audit entries for every transactional record.
 * Uses pessimistic locking to prevent race conditions when hundreds of schools
 * submit reports simultaneously.
 *
 * Chain structure:
 *   Entry N: current_hash = SHA256(payload), previous_hash = Entry(N-1).current_hash
 *   Entry 1: previous_hash = SHA256('genesis')
 */
final class LedgerService
{
    private const GENESIS_HASH = 'genesis';

    /**
     * Record a new entry in the audit ledger within a DB transaction.
     *
     * @param  Model  $entity  The model instance that was just created (dispatch, receipt, feedback)
     * @param  Request  $request  Current HTTP request (for IP/UA metadata)
     * @param  string|null  $actorId  Admin UUID or null for anonymous ZKP
     * @param  string|null  $actorType  'admin' or 'zkp_anonymous'
     * @return AuditLedger The created ledger entry
     *
     * @throws \Throwable On transaction failure
     */
    public function record(
        Model $entity,
        Request $request,
        ?string $actorId = null,
        ?string $actorType = null,
    ): AuditLedger {
        return DB::transaction(function () use ($entity, $request, $actorId, $actorType): AuditLedger {
            // Pessimistic lock: lock the latest ledger row to prevent race conditions.
            // This serializes concurrent writes to the ledger sequence.
            $lastEntry = AuditLedger::query()
                ->orderByDesc('sequence_number')
                ->lockForUpdate()
                ->first();

            $previousHash = $lastEntry
                ? $lastEntry->current_hash
                : hash('sha256', self::GENESIS_HASH);

            $nextSequence = $lastEntry
                ? $lastEntry->sequence_number + 1
                : 1;

            // Build payload snapshot — full record at time of creation
            $payload = $entity->toArray();
            $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            // Compute SHA-256 hash of the payload
            $currentHash = hash('sha256', $payloadJson);

            // Create the immutable ledger entry
            $ledger = new AuditLedger;
            $ledger->id = (string) Str::uuid();
            $ledger->sequence_number = $nextSequence;
            $ledger->entity_type = $entity->getTable();
            $ledger->entity_id = $entity->getKey();
            $ledger->action = 'create';
            $ledger->payload_snapshot = $payload;
            $ledger->current_hash = $currentHash;
            $ledger->previous_hash = $previousHash;
            $ledger->actor_id = $actorId;
            $ledger->actor_type = $actorType;
            $ledger->ip_address = $request->ip();
            $ledger->user_agent = $request->userAgent();
            $ledger->save();

            return $ledger;
        }, attempts: 3); // Retry up to 3 times on deadlock
    }

    /**
     * Verify the full chain integrity of the audit ledger.
     *
     * Iterates through all entries in sequence order and verifies:
     *   1. Each entry's current_hash matches SHA256 of its payload
     *   2. Each entry's previous_hash matches the prior entry's current_hash
     *   3. The chain is unbroken (no gaps in sequence numbers)
     *
     * @return array{valid: bool, entries_checked: int, first_invalid_sequence: int|null, error: string|null}
     */
    public function verifyChainIntegrity(): array
    {
        $entries = AuditLedger::query()
            ->orderBy('sequence_number')
            ->cursor(); // Memory-efficient for large datasets

        $expectedPreviousHash = hash('sha256', self::GENESIS_HASH);
        $expectedSequence = 1;
        $entriesChecked = 0;

        foreach ($entries as $entry) {
            $entriesChecked++;

            // Check sequence continuity
            if ($entry->sequence_number !== $expectedSequence) {
                return [
                    'valid' => false,
                    'entries_checked' => $entriesChecked,
                    'first_invalid_sequence' => $entry->sequence_number,
                    'error' => "Sequence gap: expected {$expectedSequence}, found {$entry->sequence_number}",
                ];
            }

            // Check previous_hash chain
            if (! hash_equals($expectedPreviousHash, $entry->previous_hash)) {
                return [
                    'valid' => false,
                    'entries_checked' => $entriesChecked,
                    'first_invalid_sequence' => $entry->sequence_number,
                    'error' => "Chain broken at sequence {$entry->sequence_number}: previous_hash mismatch",
                ];
            }

            // Verify payload hash integrity
            $recomputedHash = hash('sha256', json_encode($entry->payload_snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            if (! hash_equals($recomputedHash, $entry->current_hash)) {
                return [
                    'valid' => false,
                    'entries_checked' => $entriesChecked,
                    'first_invalid_sequence' => $entry->sequence_number,
                    'error' => "Payload tampered at sequence {$entry->sequence_number}: hash mismatch",
                ];
            }

            $expectedPreviousHash = $entry->current_hash;
            $expectedSequence++;
        }

        return [
            'valid' => true,
            'entries_checked' => $entriesChecked,
            'first_invalid_sequence' => null,
            'error' => null,
        ];
    }

    /**
     * Get the audit trail for a specific entity.
     */
    public function getEntityAuditTrail(string $entityType, string $entityId): Collection
    {
        return AuditLedger::query()
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->orderBy('sequence_number')
            ->get();
    }
}
