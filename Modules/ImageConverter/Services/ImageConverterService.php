<?php

namespace Modules\ImageConverter\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Exception;

class ImageConverterService
{
    protected $manager;
    protected $quality;
    protected $maxWidth;
    protected $maxHeight;
    protected $cleanupDays;
    protected $storagePath;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
        $this->quality = config('imageconverter.quality', 85);
        $this->maxWidth = config('imageconverter.max_width', null);
        $this->maxHeight = config('imageconverter.max_height', null);
        $this->cleanupDays = config('imageconverter.cleanup_days', 7);
        $this->storagePath = config('imageconverter.storage_path', 'public/converted');
    }

    /**
     * Convert the uploaded image to JPEG and store it.
     *
     * @param UploadedFile $file
     * @return array
     * @throws Exception
     */
    public function convert(UploadedFile $file): array
    {
        try {
            // Create unique filename
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $sanitizedName = $this->sanitizeFilename($originalName);
            $filename = $sanitizedName . '_' . time() . '_' . uniqid() . '.jpg';
        
            // Read and process image
            $image = $this->manager->read($file);
        
            // Resize if max dimensions are set
            if ($this->maxWidth || $this->maxHeight) {
                $image->scale(
                    width: $this->maxWidth,
                    height: $this->maxHeight
                );
            }
        
            // Encode to JPEG with quality setting
            $encodedImage = $image->toJpeg(quality: $this->quality);
        
            // Store the image in public/converted
            Storage::disk('public')->put('converted/' . $filename, $encodedImage);
        
            // Get file info
            $fileSize = Storage::disk('public')->size('converted/' . $filename);
            $publicUrl = asset('storage/converted/' . $filename);
        
            // Clean up old files (optional)
            if ($this->cleanupDays > 0) {
                $this->cleanupOldFiles($this->cleanupDays);
            }
        
            return [
                'success' => true,
                'filename' => $filename,
                'url' => $publicUrl,
                'size' => $fileSize,
                'size_human' => $this->formatBytes($fileSize),
            ];
        
        } catch (Exception $e) {
            throw new Exception('Failed to convert image: ' . $e->getMessage());
        }
        
    }

    /**
     * Sanitize filename to prevent issues
     *
     * @param string $filename
     * @return string
     */
    protected function sanitizeFilename(string $filename): string
    {
        // Remove special characters and spaces
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
        // Remove multiple underscores
        $filename = preg_replace('/_+/', '_', $filename);
        // Trim underscores from start and end
        $filename = trim($filename, '_');
        // Limit length
        return substr($filename, 0, 50);
    }

    /**
     * Clean up files older than specified days
     *
     * @param int $days
     * @return void
     */
    protected function cleanupOldFiles(int $days): void
    {
        try {
            $files = Storage::files($this->storagePath);
            $threshold = now()->subDays($days)->timestamp;

            foreach ($files as $file) {
                if (Storage::lastModified($file) < $threshold) {
                    Storage::delete($file);
                }
            }
        } catch (Exception $e) {
            // Silently fail - this is not critical
            \Illuminate\Support\Facades\Log::warning('Failed to cleanup old files: ' . $e->getMessage());
        }
    }

    /**
     * Format bytes to human readable format
     *
     * @param int $bytes
     * @return string
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}