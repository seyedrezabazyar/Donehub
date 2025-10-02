<?php

namespace Modules\HtmlToPdf\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\HtmlToPdf\Services\HtmlToPdfService;
use Modules\HtmlToPdf\Http\Requests\ConvertHtmlRequest;
use Illuminate\Support\Facades\Log;

class HtmlToPdfController extends Controller
{
    protected $htmlToPdfService;

    public function __construct(HtmlToPdfService $htmlToPdfService)
    {
        $this->htmlToPdfService = $htmlToPdfService;
    }

    /**
     * Handles the HTML to PDF conversion request.
     *
     * @param ConvertHtmlRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function convert(ConvertHtmlRequest $request)
    {
        try {
            $file = $request->file('file');

            // Get original file info
            $originalSize = $file->getSize();
            $originalName = $file->getClientOriginalName();

            // Convert HTML to PDF
            $result = $this->htmlToPdfService->convert($file);

            return response()->json([
                'status' => 'success',
                'message' => 'File converted successfully to PDF.',
                'data' => [
                    'download_url' => url($result['url']),
                    'filename' => $result['filename'],
                    'size' => $result['size'],
                    'size_human' => $this->formatBytes($result['size']),
                    'original_filename' => $originalName,
                    'original_size' => $originalSize,
                    'original_size_human' => $this->formatBytes($originalSize),
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('HTML to PDF conversion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred during file conversion.',
                'error' => config('app.debug') ? $e->getMessage() : 'A server error occurred.',
            ], 500);
        }
    }

    /**
     * Format bytes to human readable format.
     *
     * @param int $bytes
     * @return string
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}