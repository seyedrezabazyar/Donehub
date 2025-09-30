<?php

namespace Modules\ImageConverter\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ImageConverter\Services\ImageConverterService;

class ImageController extends Controller
{
    protected $imageConverterService;

    public function __construct(ImageConverterService $imageConverterService)
    {
        $this->imageConverterService = $imageConverterService;
    }

    public function __invoke(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120', // 5MB max size
        ]);

        $file = $request->file('image');

        try {
            $downloadUrl = $this->imageConverterService->convert($file);

            return response()->json([
                'status' => 'success',
                'message' => 'Image converted successfully.',
                'download_url' => url($downloadUrl),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to convert image.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}