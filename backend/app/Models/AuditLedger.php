<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\AppendOnly;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

final class AuditLedger extends Model
{
    use AppendOnly, HasUuid;

    const UPDATED_AT = null;

    protected $fillable = [
        'sequence_number',
        'entity_type',
        'entity_id',
        'action',
        'payload_snapshot',
        'current_hash',
        'previous_hash',
        'actor_id',
        'actor_type',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'sequence_number' => 'integer',
        'payload_snapshot' => 'array',
    ];

    /**
     * Verify the integrity of this ledger entry.
     */
    public function verifyHash(): bool
    {
        $computed = hash('sha256', json_encode($this->payload_snapshot));

        return hash_equals($this->current_hash, $computed);
    }
}
