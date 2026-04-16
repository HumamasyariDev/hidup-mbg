<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\AppendOnly;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SchoolReceipt extends Model
{
    use AppendOnly, HasFactory, HasUuid;

    const UPDATED_AT = null;

    protected $fillable = [
        'daily_dispatch_id',
        'school_id',
        'receipt_date',
        'quantity_received',
        'quantity_distributed',
        'quantity_damaged',
        'condition',
        'notes',
        'photo_proof_path',
        'reported_by_admin_id',
        'reporter_latitude',
        'reporter_longitude',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'quantity_received' => 'integer',
        'quantity_distributed' => 'integer',
        'quantity_damaged' => 'integer',
        'reporter_latitude' => 'decimal:7',
        'reporter_longitude' => 'decimal:7',
    ];

    public function dailyDispatch(): BelongsTo
    {
        return $this->belongsTo(DailyDispatch::class, 'daily_dispatch_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reported_by_admin_id');
    }
}
