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
        // مسیر ذخیره PDF ها در storage/app/public/converted_pdfs
        $this->storagePath = 'converted_pdfs';
    }

    /**
     * تبدیل HTML به PDF با پشتیبانی از منابع خارجی
     *
     * @param UploadedFile $file
     * @return array
     * @throws Exception
     */
    public function convert(UploadedFile $file): array
    {
        try {
            $htmlContent = file_get_contents($file->getRealPath());

            // اگر میخوای تصاویر محلی رو هم embed کنی، میتونی این متد رو اضافه کنی
            $htmlContent = $this->embedLocalImages($htmlContent, dirname($file->getRealPath()));

            // نام فایل PDF
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $sanitizedName = $this->sanitizeFilename($originalName);
            $filename = $sanitizedName . '_' . time() . '.pdf';

            // تبدیل HTML به PDF با اجازه بارگذاری منابع خارجی
            $pdf = Pdf::loadHtml($htmlContent)
                ->setOption([
                    'isRemoteEnabled' => true,      // منابع خارجی فعال
                    'isHtml5ParserEnabled' => true
                ])
                ->setPaper('a4', 'portrait');

            $output = $pdf->output();
            $fullPath = $this->storagePath . '/' . $filename;

            Storage::disk('public')->put($fullPath, $output);

            $fileSize = Storage::disk('public')->size($fullPath);
            $publicUrl = Storage::disk('public')->url($fullPath);

            return [
                'success' => true,
                'filename' => $filename,
                'url' => $publicUrl,
                'size' => $fileSize,
                'original_filename' => $file->getClientOriginalName(),
                'original_size' => $file->getSize(),
            ];

        } catch (Exception $e) {
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

    /**
     * تبدیل تصاویر محلی <img src="..."> به Base64
     */
    protected function embedLocalImages(string $html, string $baseDir): string
    {
        return preg_replace_callback('/<img\s+[^>]*src="([^"]+)"[^>]*>/i', function($matches) use ($baseDir) {
            $src = $matches[1];

            // اگر لینک اینترنتی است، دست نزن
            if (preg_match('/^https?:\/\//', $src)) {
                return $matches[0];
            }

            $filePath = realpath($baseDir . '/' . $src);
            if (!$filePath || !file_exists($filePath)) {
                return $matches[0];
            }

            $type = pathinfo($filePath, PATHINFO_EXTENSION);
            $data = base64_encode(file_get_contents($filePath));
            return preg_replace('/src="[^"]+"/', 'src="data:image/' . $type . ';base64,' . $data . '"', $matches[0]);
        }, $html);
    }
}
