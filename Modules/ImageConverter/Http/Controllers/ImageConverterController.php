<?php

namespace Modules\ImageConverter\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\ImageConverter\Services\ImageConverterService;
use Modules\ImageConverter\Http\Requests\ConvertImageRequest;
use Illuminate\Support\Facades\Log;

class ImageConverterController extends Controller
{
    protected $imageConverterService;

    public function __construct(ImageConverterService $imageConverterService)
    {
        $this->imageConverterService = $imageConverterService;
    }

    /**
     * Handles the image conversion request.
     *
     * @param ConvertImageRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function convert(ConvertImageRequest $request)
    {
        try {
            $file = $request->file('image');

            // Get original file info
            $originalSize = $file->getSize();
            $originalFormat = $file->getClientOriginalExtension();

            // Convert image
            $result = $this->imageConverterService->convert($file);

            return response()->json([
                'status' => 'success',
                'message' => 'Image converted successfully to JPG.',
                'data' => [
                    'download_url' => url($result['url']),
                    'filename' => $result['filename'],
                    'size' => $result['size'],
                    'size_human' => $result['size_human'],
                    'original_format' => strtoupper($originalFormat),
                    'original_size' => $originalSize,
                    'original_size_human' => $this->formatBytes($originalSize),
                    'compression_ratio' => $this->calculateCompressionRatio($originalSize, $result['size']),
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Image conversion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred during image conversion.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Calculate compression ratio
     *
     * @param int $originalSize
     * @param int $compressedSize
     * @return string
     */
    protected function calculateCompressionRatio(int $originalSize, int $compressedSize): string
    {
        if ($originalSize == 0) return '0%';

        $ratio = (($originalSize - $compressedSize) / $originalSize) * 100;
        return round($ratio, 2) . '%';
    }

    /**
     * Format bytes to human readable
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