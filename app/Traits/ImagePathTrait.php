<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait ImagePathTrait
{
    /**
     * Convert a file path into a full URL.
     * Works for both public and storage uploaded files.
     */
    public function fullImageUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        // If already URL, return as-is
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // Normalize path: replace backslashes and reduce multiple slashes
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('#/+#', '/', $path);

        // Remove leading slash for consistency
        $path = ltrim($path, '/');

        // 1. Files explicitly in 'public' or 'uploads' or 'assets' directories
        if (str_starts_with($path, 'uploads/') || str_starts_with($path, 'assets/')) {
            return url($path);
        }

        // 2. Storage uploaded files
        // Generate the storage URL (usually starts with '/storage/')
        $storageUrl = Storage::url($path);

        // If Storage::url returns an absolute URL (e.g., S3 disk), return it as-is
        if (filter_var($storageUrl, FILTER_VALIDATE_URL)) {
            return $storageUrl;
        }

        // Otherwise, wrap the relative storage URL with the application's base URL
        return url(ltrim($storageUrl, '/'));
    }

    /**
     * @deprecated Use fullImageUrl() instead.
     */
    public function fullImageUrlForApi(?string $path): ?string
    {
        return $this->fullImageUrl($path);
    }
}
