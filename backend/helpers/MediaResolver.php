<?php

declare(strict_types=1);

namespace ConnectMyUni\Helpers;

/**
 * Connect MyUni — Media Resolver
 *
 * The single place that converts a storage-relative image key (as stored in
 * database columns such as image_path / logo_path / mobile_image_path) into a
 * public URL the frontend can load.
 *
 * Storage-relative key examples (Phase-2 convention):
 *   events/2026/08/event-ab12cd34ef56.webp
 *   universities/2026/08/university-78a9.webp
 *   gallery/2026/08/gallery-90b1.webp
 *   hero/2026/08/hero-11c2.webp
 *
 * Static design assets (frontend/assets/images/...) are resolved here as well,
 * so one resolver serves both worlds. Absolute URLs pass through untouched.
 *
 * Swapping the local filesystem for Supabase Storage later only changes the
 * mapping inside this class — public pages never change.
 *
 * @package ConnectMyUni
 */
class MediaResolver
{
    /** Root-relative public prefix for dynamic media. */
    public const STORAGE_PREFIX = 'storage/uploads/';

    /**
     * Convert any stored key/path/URL into a public, root-relative URL.
     *
     * - null / empty                -> '' (caller decides fallback)
     * - absolute URLs / data URIs   -> returned unchanged
     * - root-relative URLs          -> returned unchanged
     * - frontend/assets/...         -> static asset URL
     * - storage/uploads/...         -> dynamic media URL
     * - bare key (events/...)       -> dynamic media URL under storage/uploads
     * - path-traversal attempts     -> '' (rejected)
     */
    public static function url(?string $key): string
    {
        $key = trim((string) $key);
        if ($key === '') {
            return '';
        }

        if (self::containsTraversal($key)) {
            return '';
        }

        // Absolute URL or data URI — pass through untouched.
        if (preg_match('#^(https?:)?//#i', $key) || str_starts_with($key, 'data:')) {
            return $key;
        }

        // Root-relative URL already (e.g. /ConnectMyUni/frontend/...).
        if ($key[0] === '/') {
            return $key;
        }

        $base = rtrim(CONNECTMYUNI_BASE_URL, '/');

        // Static design asset.
        if (str_starts_with($key, 'frontend/assets/')) {
            return $base . '/' . $key;
        }

        // Dynamic media that already carries the storage prefix.
        if (str_starts_with($key, self::STORAGE_PREFIX) || str_starts_with($key, 'storage/')) {
            return $base . '/' . ltrim($key, '/');
        }

        // Bare storage-relative key -> storage/uploads/<key>.
        return $base . '/' . self::STORAGE_PREFIX . $key;
    }

    /**
     * Absolute local filesystem path for a storage-relative key, or null when
     * the key is empty/invalid. Used by the upload service and deletion.
     */
    public static function path(?string $key): ?string
    {
        $key = trim((string) $key);
        if ($key === '' || self::containsTraversal($key)) {
            return null;
        }

        $key = ltrim($key, '/');
        if (str_starts_with($key, self::STORAGE_PREFIX)) {
            $key = substr($key, strlen(self::STORAGE_PREFIX));
        } elseif (str_starts_with($key, 'storage/')) {
            $key = substr($key, strlen('storage/'));
        }

        if ($key === '') {
            return null;
        }

        return dirname(__DIR__, 2) . '/' . self::STORAGE_PREFIX . $key;
    }

    /**
     * True when the value is a dynamic, admin-managed media key (as opposed to
     * a static design asset or an external URL).
     */
    public static function isDynamic(?string $key): bool
    {
        $key = trim((string) $key);
        return $key !== ''
            && !preg_match('#^(https?:)?//#i', $key)
            && !str_starts_with($key, 'data:')
            && !str_starts_with($key, 'frontend/')
            && !self::containsTraversal($key);
    }

    /** Reject parent-directory traversal tokens. */
    private static function containsTraversal(string $key): bool
    {
        return str_contains($key, '..');
    }
}