<?php

declare(strict_types=1);

namespace ConnectMyUni\Services;

use ConnectMyUni\Helpers\MediaResolver;

/**
 * Connect MyUni — Secure Media Upload Service
 *
 * Local-filesystem implementation of the media write-path. Validates uploads
 * strictly (real MIME via finfo, safe extensions, size cap, generated
 * filenames) and stores files under storage/uploads/<category>/<year>/<month>/,
 * returning a storage-relative key such as:
 *
 *   events/2026/08/event-ab12cd34ef56.webp
 *
 * Public pages never touch the filesystem — they resolve keys via
 * MediaResolver::url(). Swapping to Supabase Storage later means replacing the
 * internals of this class (and the resolver), not the pages.
 *
 * @package ConnectMyUni
 */
class MediaService
{
    /** Maximum accepted upload size (5 MB). */
    public const MAX_SIZE = 5 * 1024 * 1024;

    /** Allowed image MIME types mapped to safe file extensions. */
    private const ALLOWED_MIME = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    /** Logical categories -> destination directory under storage/uploads/. */
    private const CATEGORY_DIRS = [
        'events'       => 'events',
        'universities' => 'universities',
        'gallery'      => 'gallery',
        'hero'         => 'hero',
        'countries'    => 'countries',
    ];

    /** Generated filename prefix per category (never user-supplied). */
    private const CATEGORY_PREFIX = [
        'events'       => 'event',
        'universities' => 'university',
        'gallery'      => 'gallery',
        'hero'         => 'hero',
        'countries'    => 'country',
    ];

    /**
     * Validate + store an uploaded file.
     *
     * @param array  $file     A single $_FILES entry.
     * @param string $category events|universities|gallery|hero|countries
     * @return array{key:?string,error:?string} storage-relative key, or error message.
     */
    public function store(array $file, string $category): array
    {
        if (!isset(self::CATEGORY_DIRS[$category])) {
            return ['key' => null, 'error' => 'Invalid upload category.'];
        }

        if (!isset($file['error'], $file['tmp_name'], $file['name'], $file['size'])) {
            return ['key' => null, 'error' => 'No file was uploaded.'];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['key' => null, 'error' => $this->uploadErrorMessage($file['error'])];
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return ['key' => null, 'error' => 'Uploaded file is not a valid upload.'];
        }

        // Size guard (client + declared size).
        $declaredSize = (int) $file['size'];
        $realSize     = @filesize($file['tmp_name']);
        if ($declaredSize > self::MAX_SIZE || ($realSize !== false && $realSize > self::MAX_SIZE)) {
            return ['key' => null, 'error' => 'Image exceeds the 5 MB limit.'];
        }

        // Real MIME guard — finfo reads the actual bytes, so MIME spoofing fails.
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset(self::ALLOWED_MIME[$mime])) {
            return ['key' => null, 'error' => 'Only JPEG, PNG and WEBP images are allowed.'];
        }
        $ext = self::ALLOWED_MIME[$mime];

        // Extension guard — never trust the user-supplied filename.
        $originalName = basename((string) $file['name']);
        $originalExt  = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if ($originalExt !== '' && !in_array($originalExt, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return ['key' => null, 'error' => 'File extension is not an allowed image type.'];
        }

        // Unique, generated filename (prevents overwrites + injection).
        $name = self::CATEGORY_PREFIX[$category] . '-' . bin2hex(random_bytes(8)) . '.' . $ext;

        $relativeDir = $category . '/' . date('Y') . '/' . date('m');
        $absDir      = MediaResolver::path($relativeDir);

        if ($absDir === null || (!is_dir($absDir) && !@mkdir($absDir, 0775, true))) {
            return ['key' => null, 'error' => 'Could not create the storage directory.'];
        }

        $absPath = rtrim($absDir, '/\\') . '/' . $name;
        if (!@move_uploaded_file($file['tmp_name'], $absPath)) {
            return ['key' => null, 'error' => 'Could not save the uploaded image.'];
        }

        @chmod($absPath, 0664);

        return ['key' => $relativeDir . '/' . $name, 'error' => null];
    }

    /**
     * Delete a stored file by storage-relative key.
     *
     * @return bool false when the key is invalid or the file does not exist.
     */
    public function delete(?string $key): bool
    {
        $path = MediaResolver::path($key);
        if ($path === null || !is_file($path)) {
            return false;
        }
        return @unlink($path);
    }

    private function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The uploaded file is too large.',
            UPLOAD_ERR_PARTIAL  => 'The upload was only partially received.',
            UPLOAD_ERR_NO_FILE  => 'No file was selected.',
            UPLOAD_ERR_NO_TMP_DIR => 'The temporary upload directory is missing.',
            UPLOAD_ERR_CANT_WRITE => 'The file could not be written to disk.',
            default             => 'The upload failed. Please try again.',
        };
    }
}