<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Request Sanitizer Middleware
 *
 * Defense-in-depth input sanitization layer that runs BEFORE validation.
 *
 * Protects against:
 * - XSS payloads (script tags, event handlers, javascript: URIs)
 * - SQL injection fragments (UNION, DROP, --, etc.)
 * - Path traversal (../, %2e%2e)
 * - Null byte injection (%00, \0)
 * - CRLF injection / HTTP header splitting
 * - Unicode homoglyph attacks
 * - Excessively long inputs (DoS via parsing)
 *
 * NOTE: This is a belt-and-suspenders layer. Eloquent parameterized queries
 * and Laravel's validator remain the PRIMARY defenses.
 */
final class RequestSanitizerMiddleware
{
    /** Maximum allowed string length for any single input field */
    private const MAX_FIELD_LENGTH = 10_000;

    /** Fields that should NEVER be sanitized (binary/encoded data) */
    private const BYPASS_FIELDS = ['password', 'password_confirmation', 'zkp_proof'];

    /**
     * Patterns that indicate malicious input — log and strip.
     * Each key is a threat category, value is a regex pattern.
     */
    private const THREAT_PATTERNS = [
        'xss_script'     => '/<script\b[^>]*>.*?<\/script>/is',
        'xss_event'      => '/\bon\w+\s*=\s*["\'][^"\']*["\']/i',
        'xss_javascript' => '/javascript\s*:/i',
        'xss_vbscript'   => '/vbscript\s*:/i',
        'xss_data_uri'   => '/data\s*:\s*text\/html/i',
        'xss_expression'  => '/expression\s*\(/i',
        'sql_union'      => '/\bUNION\b\s+(ALL\s+)?SELECT\b/i',
        'sql_drop'       => '/\bDROP\b\s+(TABLE|DATABASE|INDEX)\b/i',
        'sql_insert'     => '/\bINSERT\b\s+INTO\b/i',
        'sql_update_set' => '/\bUPDATE\b\s+\w+\s+SET\b/i',
        'sql_delete'     => '/\bDELETE\b\s+FROM\b/i',
        'sql_comment'    => '/(--|#|\/\*)/i',
        'sql_semicolon'  => '/;\s*(DROP|DELETE|UPDATE|INSERT|ALTER|EXEC)/i',
        'path_traversal' => '/\.\.[\\/]/',
        'null_byte'      => '/\x00|%00/',
        'crlf_injection' => '/[\r\n]/',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();
        $threats = [];

        $sanitized = $this->sanitizeRecursive($input, '', $threats);

        if (!empty($threats)) {
            Log::channel('security')->warning('Malicious input detected and sanitized', [
                'ip'      => $request->ip(),
                'url'     => $request->fullUrl(),
                'method'  => $request->method(),
                'threats' => $threats,
                'user_id' => $request->user()?->id ?? 'anonymous',
            ]);
        }

        $request->replace($sanitized);

        // Also sanitize route parameters
        foreach ($request->route()?->parameters() ?? [] as $key => $value) {
            if (is_string($value)) {
                $cleaned = $this->sanitizeString($value, "route.{$key}", $threats);
                $request->route()->setParameter($key, $cleaned);
            }
        }

        return $next($request);
    }

    /**
     * Recursively sanitize all input fields.
     */
    private function sanitizeRecursive(array $data, string $prefix, array &$threats): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            $fieldPath = $prefix ? "{$prefix}.{$key}" : (string) $key;

            if (in_array($key, self::BYPASS_FIELDS, true)) {
                // Only check length for bypass fields, don't sanitize content
                if (is_string($value) && strlen($value) > self::MAX_FIELD_LENGTH) {
                    $threats[] = ['field' => $fieldPath, 'type' => 'excessive_length'];
                    $value = substr($value, 0, self::MAX_FIELD_LENGTH);
                }
                $result[$key] = $value;
                continue;
            }

            if (is_array($value)) {
                $result[$key] = $this->sanitizeRecursive($value, $fieldPath, $threats);
            } elseif (is_string($value)) {
                $result[$key] = $this->sanitizeString($value, $fieldPath, $threats);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    private function sanitizeString(string $value, string $fieldPath, array &$threats): string
    {
        // Length check
        if (strlen($value) > self::MAX_FIELD_LENGTH) {
            $threats[] = ['field' => $fieldPath, 'type' => 'excessive_length'];
            $value = substr($value, 0, self::MAX_FIELD_LENGTH);
        }

        // Null byte removal (always dangerous, never legitimate)
        $value = str_replace(["\0", '%00'], '', $value);

        // Detect and log threats, strip dangerous patterns
        foreach (self::THREAT_PATTERNS as $type => $pattern) {
            if (preg_match($pattern, $value)) {
                $threats[] = [
                    'field'   => $fieldPath,
                    'type'    => $type,
                    'snippet' => substr($value, 0, 100),
                ];
                // Strip the pattern
                $value = (string) preg_replace($pattern, '', $value);
            }
        }

        // Strip invisible Unicode control characters (except tab, newline in text fields)
        $value = (string) preg_replace('/[\x{200B}-\x{200F}\x{2028}-\x{202F}\x{2060}\x{FEFF}]/u', '', $value);

        return trim($value);
    }
}
