<?php

namespace Modules\HtmlToPdf\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;

class HtmlToPdfService
{
    protected $storagePath;

    public function __construct()
    {
        // Define the storage path for converted PDFs. This could be moved to a config file.
        $this->storagePath = 'converted_pdfs';
    }

    /**
     * Convert the uploaded HTML file to PDF and store it.
     *
     * @param UploadedFile $file
     * @return array
     * @throws Exception
     */
    public function convert(UploadedFile $file): array
    {
        try {
            // Read HTML content from the uploaded file
            $htmlContent = $file->get();

            // Sanitize original filename and create a unique new filename
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $sanitizedName = $this->sanitizeFilename($originalName);
            $filename = $sanitizedName . '_' . time() . '.pdf';

            // Configure and load HTML into DOMPDF
            $pdf = PDF::loadHtml($htmlContent)
                ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true])
                ->setPaper('a4', 'portrait');

            // Get the PDF content as a string
            $output = $pdf->output();

            // Define the full path for storage
            $fullPath = $this->storagePath . '/' . $filename;

            // Store the PDF in the public disk
            Storage::disk('public')->put($fullPath, $output);

            // Get file info
            $fileSize = Storage::disk('public')->size($fullPath);
            $publicUrl = Storage::disk('public')->url($fullPath);

            return [
                'success' => true,
                'filename' => $filename,
                'url' => $publicUrl,
                'size' => $fileSize,
            ];

        } catch (Exception $e) {
            // Rethrow a more specific exception
            throw new Exception('Failed to convert HTML to PDF: ' . $e->getMessage());
        }
    }

    /**
     * Sanitize a filename to make it safe for storage.
     *
     * @param string $filename
     * @return string
     */
    protected function sanitizeFilename(string $filename): string
    {
        // Remove characters that are not letters, numbers, underscores, or hyphens
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
        // Replace multiple underscores or hyphens with a single one
        $filename = preg_replace('/[_-]+/', '_', $filename);
        // Trim leading/trailing underscores
        $filename = trim($filename, '_');
        // Limit filename length to prevent issues
        return substr($filename, 0, 100);
    }
}