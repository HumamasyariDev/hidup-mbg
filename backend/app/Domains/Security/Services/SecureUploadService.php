<?php

declare(strict_types=1);

namespace App\Domains\Security\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Secure File Upload Service
 *
 * Defense-in-depth file upload handling:
 * 1. MIME type validation (content-sniffed, not just extension)
 * 2. File extension whitelist
 * 3. Maximum file size enforcement
 * 4. Filename sanitization (prevent path traversal, null bytes)
 * 5. UUID-based rename (prevent overwrites + information leakage)
 * 6. Private disk storage (not publicly accessible)
 * 7. Image dimension validation (prevent zip bombs disguised as images)
 * 8. Double extension detection (photo.php.jpg)
 */
final class SecureUploadService
{
    /** Allowed MIME types mapped to their safe extensions */
    private const ALLOWED_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/heic' => 'heic',
        'image/heif' => 'heif',
    ];

    /** Maximum file size in bytes (10 MB) */
    private const MAX_SIZE_BYTES = 10 * 1024 * 1024;

    /** Maximum image dimensions */
    private const MAX_DIMENSION = 8000;

    /** Disk to store files on (must be a non-public disk) */
    private const STORAGE_DISK = 'local';

    /**
     * Validate and store an uploaded file securely.
     *
     * @param UploadedFile $file     The uploaded file
     * @param string       $category Storage subdirectory (e.g., 'dispatch_proofs', 'feedback_photos')
     *
     * @return array{path: string, original_name: string, size: int, mime: string}
     *
     * @throws \InvalidArgumentException If file fails any security check
     */
    public function store(UploadedFile $file, string $category): array
    {
        // 1. Size check
        if ($file->getSize() > self::MAX_SIZE_BYTES) {
            throw new \InvalidArgumentException(
                'Ukuran file melebihi batas maksimum (' . (self::MAX_SIZE_BYTES / 1024 / 1024) . ' MB).'
            );
        }

        // 2. MIME type validation (content-based, not trusted extension)
        $detectedMime = $file->getMimeType();
        if (!array_key_exists($detectedMime, self::ALLOWED_TYPES)) {
            Log::channel('security')->warning('Rejected file upload: invalid MIME type', [
                'detected_mime'  => $detectedMime,
                'original_name'  => $file->getClientOriginalName(),
                'client_mime'    => $file->getClientMimeType(),
            ]);
            throw new \InvalidArgumentException(
                'Tipe file tidak diizinkan. Hanya JPEG, PNG, WebP, HEIC yang diterima.'
            );
        }

        // 3. Double extension detection
        $originalName = $file->getClientOriginalName();
        $extensions = explode('.', $originalName);
        if (count($extensions) > 2) {
            $dangerousExtensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'phar', 'sh', 'bash', 'exe', 'bat', 'cmd', 'com', 'js', 'jsp', 'asp', 'aspx', 'cgi', 'pl', 'py', 'rb', 'svg', 'html', 'htm', 'xml', 'xhtml'];
            foreach ($extensions as $ext) {
                if (in_array(strtolower($ext), $dangerousExtensions, true)) {
                    Log::channel('security')->alert('Blocked double-extension upload', [
                        'original_name' => $originalName,
                        'extensions'    => $extensions,
                    ]);
                    throw new \InvalidArgumentException(
                        'Nama file mengandung ekstensi berbahaya.'
                    );
                }
            }
        }

        // 4. Image dimension check (prevents decompression bombs)
        $imageInfo = @getimagesize($file->getPathname());
        if ($imageInfo !== false) {
            [$width, $height] = $imageInfo;
            if ($width > self::MAX_DIMENSION || $height > self::MAX_DIMENSION) {
                throw new \InvalidArgumentException(
                    "Dimensi gambar terlalu besar (max {self::MAX_DIMENSION}x{self::MAX_DIMENSION}px)."
                );
            }
        }

        // 5. Check file content for PHP tags (server-side code injection)
        $fileContent = file_get_contents($file->getPathname(), false, null, 0, 1024);
        if ($fileContent !== false && preg_match('/<\?php|<\?=|<%/i', $fileContent)) {
            Log::channel('security')->alert('Blocked file with embedded PHP code', [
                'original_name' => $originalName,
            ]);
            throw new \InvalidArgumentException('File mengandung kode berbahaya.');
        }

        // 6. Generate safe filename: UUID + safe extension
        $safeExtension = self::ALLOWED_TYPES[$detectedMime];
        $safeFilename = Str::uuid()->toString() . '.' . $safeExtension;

        // 7. Sanitize category path (prevent path traversal)
        $category = preg_replace('/[^a-zA-Z0-9_\-]/', '', $category);

        // 8. Store in PRIVATE disk, NOT public
        $storagePath = "uploads/{$category}/" . now()->format('Y/m');
        $fullPath = $file->storeAs($storagePath, $safeFilename, self::STORAGE_DISK);

        Log::info('File uploaded securely', [
            'path'          => $fullPath,
            'original_name' => $originalName,
            'mime'          => $detectedMime,
            'size'          => $file->getSize(),
        ]);

        return [
            'path'          => $fullPath,
            'original_name' => $originalName,
            'size'          => $file->getSize(),
            'mime'          => $detectedMime,
        ];
    }

    /**
     * Generate a temporary signed URL for private file access.
     *
     * @param string $path    Storage path
     * @param int    $minutes URL lifetime in minutes
     */
    public function temporaryUrl(string $path, int $minutes = 15): string
    {
        // For local disk, generate a signed route instead
        return url()->temporarySignedRoute(
            'secure-file.download',
            now()->addMinutes($minutes),
            ['path' => encrypt($path)]
        );
    }

    /**
     * Safely delete a file.
     */
    public function delete(string $path): bool
    {
        if (!Storage::disk(self::STORAGE_DISK)->exists($path)) {
            return false;
        }

        return Storage::disk(self::STORAGE_DISK)->delete($path);
    }
}
