<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

final class School extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'name',
        'npsn',
        'address',
        'city',
        'province',
        'phone',
        'email',
        'level',
        'student_count',
        'geofence_radius_meters',
        'sppg_provider_id',
        'is_active',
    ];

    protected $casts = [
        'student_count' => 'integer',
        'geofence_radius_meters' => 'integer',
        'is_active' => 'boolean',
    ];

    // --- Relationships ---

    public function sppgProvider(): BelongsTo
    {
        return $this->belongsTo(SppgProvider::class, 'sppg_provider_id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(SchoolReceipt::class, 'school_id');
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(UserFeedback::class, 'school_id');
    }

    // --- Spatial Helpers ---

    public static function setCoordinate(string $id, float $latitude, float $longitude): void
    {
        DB::statement(
            "UPDATE schools SET coordinate = ST_GeomFromText(CONCAT('POINT(', ?, ' ', ?, ')')) WHERE id = ?",
            [$longitude, $latitude, $id]
        );
    }

    /**
     * Check if a given point is within this school's geofence radius.
     */
    public static function isWithinGeofence(string $schoolId, float $latitude, float $longitude): bool
    {
        $result = DB::selectOne(
            "SELECT ST_Distance_Sphere(coordinate, ST_GeomFromText(CONCAT('POINT(', ?, ' ', ?, ')'))) AS distance_m, geofence_radius_meters
             FROM schools WHERE id = ?",
            [$longitude, $latitude, $schoolId]
        );

        if (! $result) {
            return false;
        }

        return $result->distance_m <= $result->geofence_radius_meters;
    }

    public function scopeWithinRadius(
        Builder $query,
        float $latitude,
        float $longitude,
        int $meters = 100
    ): Builder {
        return $query->whereRaw(
            "ST_Distance_Sphere(coordinate, ST_GeomFromText(CONCAT('POINT(', ?, ' ', ?, ')'))) <= ?",
            [$longitude, $latitude, $meters]
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
