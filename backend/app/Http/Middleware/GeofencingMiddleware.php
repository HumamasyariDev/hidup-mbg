<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\School;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Strict Geofencing Middleware
 *
 * Validates that the request originates from within an allowed radius
 * of a registered school or SPPG provider using MySQL ST_Distance_Sphere.
 *
 * Usage in routes:
 *   ->middleware('geofencing:school,{school_id}')
 *   ->middleware('geofencing:sppg,{sppg_provider_id}')
 *
 * Request must include headers or body params:
 *   X-Geo-Latitude / X-Geo-Longitude  (headers, preferred)
 *   OR latitude / longitude            (request body)
 */
final class GeofencingMiddleware
{
    /**
     * @param  string  $entityType  'school' or 'sppg'
     * @param  string|null  $entityIdParam  Route parameter name containing the entity UUID
     */
    public function handle(Request $request, Closure $next, string $entityType = 'school', ?string $entityIdParam = null): Response
    {
        $latitude = $this->extractCoordinate($request, 'latitude');
        $longitude = $this->extractCoordinate($request, 'longitude');

        if ($latitude === null || $longitude === null) {
            return response()->json([
                'error' => 'geofencing_required',
                'message' => 'Koordinat perangkat (latitude/longitude) wajib disertakan.',
            ], 422);
        }

        // Basic coordinate sanity check
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            return response()->json([
                'error' => 'invalid_coordinates',
                'message' => 'Koordinat tidak valid.',
            ], 422);
        }

        $entityId = $entityIdParam ? $request->route($entityIdParam) : null;

        $result = $this->calculateDistance($entityType, $entityId, $latitude, $longitude);

        if ($result === null) {
            return response()->json([
                'error' => 'entity_not_found',
                'message' => 'Lokasi referensi tidak ditemukan.',
            ], 404);
        }

        $allowedRadius = $result->allowed_radius;
        $distanceMeters = (float) $result->distance_m;

        if ($distanceMeters > $allowedRadius) {
            return response()->json([
                'error' => 'outside_geofence',
                'message' => "Anda berada di luar radius yang diizinkan ({$allowedRadius}m). Jarak terdeteksi: ".round($distanceMeters, 1).'m.',
                'distance_m' => round($distanceMeters, 1),
                'allowed_radius_m' => $allowedRadius,
            ], 403);
        }

        // Attach validated coordinates to request for downstream use
        $request->merge([
            'validated_latitude' => $latitude,
            'validated_longitude' => $longitude,
            'geo_distance_m' => $distanceMeters,
        ]);

        return $next($request);
    }

    private function extractCoordinate(Request $request, string $field): ?float
    {
        // Prefer headers (harder to tamper in-browser)
        $headerKey = 'X-Geo-'.ucfirst($field);
        $value = $request->header($headerKey) ?? $request->input($field);

        if ($value === null || ! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function calculateDistance(string $entityType, ?string $entityId, float $lat, float $lng): ?object
    {
        if ($entityType === 'school') {
            $query = 'SELECT
                        ST_Distance_Sphere(coordinate, ST_SRID(POINT(?, ?), 4326)) AS distance_m,
                        geofence_radius_meters AS allowed_radius
                      FROM schools WHERE id = ? AND is_active = 1';

            return DB::selectOne($query, [$lng, $lat, $entityId]);
        }

        if ($entityType === 'sppg') {
            // SPPG providers use a default 100m radius
            $defaultRadius = 100;
            $query = 'SELECT
                        ST_Distance_Sphere(coordinate, ST_SRID(POINT(?, ?), 4326)) AS distance_m,
                        ? AS allowed_radius
                      FROM sppg_providers WHERE id = ? AND is_active = 1';

            return DB::selectOne($query, [$lng, $lat, $defaultRadius, $entityId]);
        }

        return null;
    }
}
