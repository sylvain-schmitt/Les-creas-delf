<?php

namespace App\Service;

use App\Model\Media;
use Ogan\Image\ImageOptimizer;
use Ogan\Http\UploadedFile;

class MediaService
{
    private ImageOptimizer $optimizer;
    private string $uploadDir;

    public function __construct()
    {
        $this->optimizer = new ImageOptimizer();
        $this->uploadDir = dirname(__DIR__, 2) . '/public/uploads';
    }

    /**
     * Upload an image with thumbnails in a unique UUID folder
     */
    public function upload(array $file, int $userId, ?string $alt = null): ?Media
    {
        // Validate file
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Validate MIME type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $allowedTypes)) {
            return null;
        }

        // Generate unique folder UUID
        $uuid = $this->generateUuid();
        $folderPath = $this->uploadDir . '/' . $uuid;

        // Create folder
        if (!mkdir($folderPath, 0755, true)) {
            return null;
        }

        try {
            // Create UploadedFile from $_FILES array
            $uploadedFile = new UploadedFile($file);

            // Optimize with thumbnails
            // Le filename 'original' donnera: original.webp, original_thumbnail.webp, etc.
            $results = $this->optimizer->optimizeWithThumbnails($uploadedFile, [
                'thumbnail' => ['width' => 150, 'height' => 150, 'crop' => true],
                'medium' => ['width' => 600],
                'large' => ['width' => 1200],
            ], [
                'directory' => $folderPath . '/',
                'format' => 'webp',
                'quality' => 85,
                'filename' => 'original.webp', // Force le nom de base
            ]);

            // Save to database
            $media = new Media();
            $media->setFilename('original.webp');
            $media->setOriginalName($file['name']);
            $media->setPath($uuid); // Store only UUID as path
            $media->setMimeType('image/webp');
            $media->setSize($results['original']->size ?? $file['size']);
            $media->setAlt($alt ?? pathinfo($file['name'], PATHINFO_FILENAME));
            $media->setUserId($userId);
            $media->setCreatedAt(new \DateTime());
            $media->save();

            return $media;
        } catch (\Exception $e) {
            // Cleanup on error
            $this->deleteFolder($folderPath);
            return null;
        }
    }

    /**
     * Delete a media and its entire folder
     */
    public function delete(Media $media): bool
    {
        $folderPath = $this->uploadDir . '/' . $media->getPath();

        // Delete folder and all contents
        if (is_dir($folderPath)) {
            $this->deleteFolder($folderPath);
        }

        // Delete from database
        $media->delete();

        return true;
    }

    /**
     * Get URL for a specific size
     */
    public function getUrl(Media $media, string $size = 'original'): string
    {
        $filename = match ($size) {
            'thumbnail' => 'thumbnail.webp',
            'medium' => 'medium.webp',
            'large' => 'large.webp',
            default => 'original.webp',
        };

        return '/uploads/' . $media->getPath() . '/' . $filename;
    }

    /**
     * Get all available URLs for a media
     */
    public function getUrls(Media $media): array
    {
        $basePath = '/uploads/' . $media->getPath();
        return [
            'original' => $basePath . '/original.webp',
            'thumbnail' => $basePath . '/thumbnail.webp',
            'medium' => $basePath . '/medium.webp',
            'large' => $basePath . '/large.webp',
        ];
    }

    /**
     * Generate a short UUID
     */
    private function generateUuid(): string
    {
        return bin2hex(random_bytes(8));
    }

    /**
     * Recursively delete a folder
     */
    private function deleteFolder(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            $filePath = $path . '/' . $file;
            if (is_dir($filePath)) {
                $this->deleteFolder($filePath);
            } else {
                unlink($filePath);
            }
        }
        rmdir($path);
    }
}
