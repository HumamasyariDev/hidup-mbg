<?php

declare(strict_types=1);

namespace App\Models\Traits;

use LogicException;

/**
 * Makes a model append-only: prevents update and delete operations.
 * Used for immutable transactional tables (dispatches, receipts, feedbacks).
 */
trait AppendOnly
{
    protected static function bootAppendOnly(): void
    {
        static::updating(function (): void {
            throw new LogicException(
                'Model ['.static::class.'] is append-only. Update operations are forbidden.'
            );
        });

        static::deleting(function (): void {
            throw new LogicException(
                'Model ['.static::class.'] is append-only. Delete operations are forbidden.'
            );
        });
    }
}
