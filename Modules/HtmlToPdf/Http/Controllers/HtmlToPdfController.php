<?php

namespace Modules\HtmlToPdf\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\HtmlToPdf\Services\HtmlToPdfService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HtmlToPdfController extends Controller
{
    protected HtmlToPdfService $pdfService;

    public function __construct(HtmlToPdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * تبدیل HTML به PDF و دانلود مستقیم فایل
     */
    public function convert(Request $request): StreamedResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:html,htm'
        ]);

        $file = $request->file('file');

        // تبدیل HTML به PDF
        $result = $this->pdfService->convert($file);

        // مسیر کامل فایل PDF در storage
        $pdfPath = storage_path('app/public/converted_pdfs/' . $result['filename']);

        // Stream کردن فایل برای دانلود مستقیم
        return response()->streamDownload(function () use ($pdfPath) {
            readfile($pdfPath);
        }, $result['filename'], [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $result['filename'] . '"',
        ]);
    }
}
