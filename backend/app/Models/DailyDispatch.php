<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\AppendOnly;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class DailyDispatch extends Model
{
    use AppendOnly, HasFactory, HasUuid;

    const UPDATED_AT = null; // Append-only: no updated_at

    protected $fillable = [
        'sppg_provider_id',
        'school_id',
        'mbg_menu_id',
        'dispatch_date',
        'quantity_sent',
        'vehicle_plate',
        'driver_name',
        'dispatched_at',
        'photo_proof_path',
        'reported_by_admin_id',
    ];

    protected $casts = [
        'dispatch_date' => 'date',
        'dispatched_at' => 'datetime',
        'quantity_sent' => 'integer',
    ];

    // --- Relationships ---

    public function sppgProvider(): BelongsTo
    {
        return $this->belongsTo(SppgProvider::class, 'sppg_provider_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(MbgMenu::class, 'mbg_menu_id');
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reported_by_admin_id');
    }

    public function receipt(): HasOne
    {
        return $this->hasOne(SchoolReceipt::class, 'daily_dispatch_id');
    }
}
