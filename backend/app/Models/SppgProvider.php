<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

final class SppgProvider extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'name',
        'license_number',
        'address',
        'city',
        'province',
        'phone',
        'email',
        'is_active',
        'capacity_per_day',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity_per_day' => 'integer',
        'license_number' => 'encrypted',
    ];

    // --- Relationships ---

    public function schools(): HasMany
    {
        return $this->hasMany(School::class, 'sppg_provider_id');
    }

    public function menus(): HasMany
    {
        return $this->hasMany(MbgMenu::class, 'sppg_provider_id');
    }

    public function dailyDispatches(): HasMany
    {
        return $this->hasMany(DailyDispatch::class, 'sppg_provider_id');
    }

    // --- Spatial Helpers ---

    /**
     * Set coordinate from lat/lng.
     */
    public static function setCoordinate(string $id, float $latitude, float $longitude): void
    {
        DB::statement(
            'UPDATE sppg_providers SET coordinate = ST_SRID(POINT(?, ?), 4326) WHERE id = ?',
            [$longitude, $latitude, $id]
        );
    }

    /**
     * Scope: find providers within a radius (meters) from a point.
     */
    public function scopeWithinRadius(
        Builder $query,
        float $latitude,
        float $longitude,
        int $meters = 100
    ): Builder {
        return $query->whereRaw(
            'ST_Distance_Sphere(coordinate, ST_SRID(POINT(?, ?), 4326)) <= ?',
            [$longitude, $latitude, $meters]
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
