<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Device Fingerprint Middleware — Anti Fake-GPS Layer 2
 *
 * Adds a second layer of protection against GPS spoofing by:
 *
 * 1. VELOCITY CHECK — If the same user/device sends coordinates from two
 *    locations faster than physically possible (>900 km/h), it's spoofing.
 *
 * 2. CONSISTENCY SCORING — Tracks a trust score per device fingerprint.
 *    Repeated anomalies (jumps, impossible speeds) degrade the score.
 *    Score below threshold = blocked.
 *
 * 3. GPS METADATA VALIDATION — Checks for suspicious signals:
 *    - Mock location flag (Android sends this header if mock is enabled)
 *    - Accuracy radius too perfect (real GPS has >3m accuracy variance)
 *    - Altitude missing or exactly 0 (spoofed GPS often omits altitude)
 *
 * Required headers from frontend:
 *   X-Device-Fingerprint: <sha256 of device characteristics>
 *   X-Geo-Accuracy: <GPS accuracy in meters>
 *   X-Geo-Altitude: <altitude in meters>
 *   X-Geo-Mock: <"true"/"false" — Android mock location flag>
 *   X-Geo-Timestamp: <GPS fix timestamp in ms>
 */
final class DeviceFingerprintMiddleware
{
    /** Maximum plausible speed in meters per second (900 km/h ~= 250 m/s) */
    private const MAX_SPEED_MPS = 250.0;

    /** Minimum trust score to allow requests (0-100 scale) */
    private const MIN_TRUST_SCORE = 30;

    /** Trust score penalty per anomaly */
    private const ANOMALY_PENALTY = 15;

    /** Trust score recovery per clean request */
    private const CLEAN_REWARD = 5;

    /** Cache TTL for location history (2 hours) */
    private const CACHE_TTL_SECONDS = 7200;

    public function handle(Request $request, Closure $next): Response
    {
        $fingerprint = $request->header('X-Device-Fingerprint');

        if (!$fingerprint || strlen($fingerprint) < 8) {
            return response()->json([
                'error' => 'device_fingerprint_required',
                'message' => 'Identifikasi perangkat diperlukan (X-Device-Fingerprint).',
            ], 422);
        }

        $anomalies = [];

        // --- Check 1: Mock Location Flag ---
        $mockFlag = $request->header('X-Geo-Mock', 'false');
        if (strtolower($mockFlag) === 'true') {
            $anomalies[] = 'mock_location_enabled';
            Log::channel('security')->alert('Mock location detected', [
                'fingerprint' => $fingerprint,
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'error' => 'mock_location_detected',
                'message' => 'Fake GPS terdeteksi. Nonaktifkan mock location.',
            ], 403);
        }

        // --- Check 2: GPS Accuracy Anomaly ---
        $accuracy = (float) $request->header('X-Geo-Accuracy', '0');
        if ($accuracy > 0 && $accuracy < 1.0) {
            // Real GPS almost never has <1m accuracy; this suggests spoofing
            $anomalies[] = 'suspiciously_perfect_accuracy';
        }

        // --- Check 3: Altitude check ---
        $altitude = $request->header('X-Geo-Altitude');
        if ($altitude === null || $altitude === '' || (float) $altitude === 0.0) {
            // Many spoofers omit altitude or send exactly 0
            $anomalies[] = 'missing_or_zero_altitude';
        }

        // --- Check 4: Velocity / Teleportation Check ---
        $lat = (float) ($request->header('X-Geo-Latitude') ?? $request->input('latitude', 0));
        $lng = (float) ($request->header('X-Geo-Longitude') ?? $request->input('longitude', 0));
        $geoTimestamp = (int) $request->header('X-Geo-Timestamp', (string) (time() * 1000));

        $cacheKey = "geo_last:{$fingerprint}";
        $lastPosition = Cache::get($cacheKey);

        if ($lastPosition && $lat !== 0.0 && $lng !== 0.0) {
            $timeDiffSeconds = abs($geoTimestamp - $lastPosition['timestamp']) / 1000;

            if ($timeDiffSeconds > 0) {
                $distanceMeters = $this->haversineDistance(
                    $lastPosition['lat'], $lastPosition['lng'],
                    $lat, $lng
                );
                $speedMps = $distanceMeters / $timeDiffSeconds;

                if ($speedMps > self::MAX_SPEED_MPS && $distanceMeters > 1000) {
                    $anomalies[] = 'teleportation_detected';
                    Log::channel('security')->warning('GPS teleportation detected', [
                        'fingerprint'   => $fingerprint,
                        'speed_kmh'     => round($speedMps * 3.6, 1),
                        'distance_km'   => round($distanceMeters / 1000, 2),
                        'time_diff_sec' => round($timeDiffSeconds, 1),
                        'from'          => [$lastPosition['lat'], $lastPosition['lng']],
                        'to'            => [$lat, $lng],
                        'ip'            => $request->ip(),
                    ]);
                }
            }
        }

        // Store current position for next velocity check
        if ($lat !== 0.0 && $lng !== 0.0) {
            Cache::put($cacheKey, [
                'lat' => $lat,
                'lng' => $lng,
                'timestamp' => $geoTimestamp,
            ], self::CACHE_TTL_SECONDS);
        }

        // --- Trust Score System ---
        $trustKey = "trust_score:{$fingerprint}";
        $trustScore = (int) Cache::get($trustKey, 100);

        if (!empty($anomalies)) {
            $penalty = count($anomalies) * self::ANOMALY_PENALTY;
            $trustScore = max(0, $trustScore - $penalty);
        } else {
            $trustScore = min(100, $trustScore + self::CLEAN_REWARD);
        }

        Cache::put($trustKey, $trustScore, self::CACHE_TTL_SECONDS);

        if ($trustScore < self::MIN_TRUST_SCORE) {
            Log::channel('security')->alert('Device blocked due to low trust score', [
                'fingerprint' => $fingerprint,
                'trust_score' => $trustScore,
                'anomalies'   => $anomalies,
                'ip'          => $request->ip(),
            ]);

            return response()->json([
                'error' => 'device_trust_too_low',
                'message' => 'Perangkat Anda terdeteksi melakukan anomali berulang. Hubungi administrator.',
            ], 403);
        }

        // Attach metadata for downstream
        $request->merge([
            'device_fingerprint' => $fingerprint,
            'device_trust_score' => $trustScore,
            'geo_anomalies'      => $anomalies,
        ]);

        return $next($request);
    }

    /**
     * Calculate distance between two points using Haversine formula.
     */
    private function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6_371_000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
           * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
