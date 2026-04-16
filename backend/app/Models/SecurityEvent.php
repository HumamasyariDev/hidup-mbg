<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\AppendOnly;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Persistent security event record.
 * Append-only at ORM + DB trigger level.
 */
final class SecurityEvent extends Model
{
    use AppendOnly, HasUuid;

    const UPDATED_AT = null;

    protected $fillable = [
        'event_type',
        'severity',
        'ip_address',
        'user_agent',
        'actor_id',
        'device_fingerprint',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Quick factory method for logging security events from anywhere.
     */
    public static function record(
        string $eventType,
        string $severity,
        Request $request,
        array $metadata = [],
        ?string $actorId = null,
    ): self {
        return self::create([
            'event_type'         => $eventType,
            'severity'           => $severity,
            'ip_address'         => $request->ip(),
            'user_agent'         => $request->userAgent(),
            'actor_id'           => $actorId ?? $request->user()?->id,
            'device_fingerprint' => $request->header('X-Device-Fingerprint'),
            'metadata'           => $metadata,
        ]);
    }
}
