<?php

namespace Modules\HtmlToPdf\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Exception;

class HtmlToPdfService
{
    protected $storagePath;

    public function __construct()
    {
        // مسیر ذخیره PDF ها در storage/app/public/converted_pdfs
        $this->storagePath = 'converted_pdfs';
    }

    /**
     * تبدیل HTML به PDF با Puppeteer
     */
    public function convert(UploadedFile $file): array
    {
        try {
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $sanitizedName = $this->sanitizeFilename($originalName);
            $filename = $sanitizedName . '_' . time() . '.pdf';
            $fullPath = Storage::disk('public')->path($this->storagePath . '/' . $filename);

            // مسیر فایل HTML آپلود شده
            $htmlPath = $file->getRealPath();

            // فرمان Node.js برای تبدیل HTML به PDF
            $process = new Process([
                '/usr/bin/node', // مسیر Node.js
                base_path('Modules/HtmlToPdf/puppeteer/pdf.js'), // مسیر اسکریپت Node
                $htmlPath,
                $fullPath
            ]);

            // اجرای Process
            $process->run();

            // لاگ کامل خروجی و خطا
            if (!$process->isSuccessful()) {
                $errorOutput = $process->getErrorOutput();
                $stdOutput = $process->getOutput();
                \Log::error("PDF conversion failed:\nSTDOUT: $stdOutput\nSTDERR: $errorOutput");
                throw new ProcessFailedException($process);
            }

            // اطلاعات فایل PDF
            $fileSize = Storage::disk('public')->size($this->storagePath . '/' . $filename);
            $publicUrl = Storage::disk('public')->url($this->storagePath . '/' . $filename);

            return [
                'success' => true,
                'filename' => $filename,
                'url' => $publicUrl,
                'size' => $fileSize,
                'original_filename' => $file->getClientOriginalName(),
                'original_size' => $file->getSize(),
            ];

        } catch (Exception $e) {
            // لاگ خطای کلی
            \Log::error("Failed to convert HTML to PDF: " . $e->getMessage());
            throw new Exception('Failed to convert HTML to PDF: ' . $e->getMessage());
        }
    }

    /**
     * امن‌سازی نام فایل برای ذخیره
     */
    protected function sanitizeFilename(string $filename): string
    {
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
        $filename = preg_replace('/[_-]+/', '_', $filename);
        return substr(trim($filename, '_'), 0, 100);
    }
}