<?php

namespace Modules\ImageConverter\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ImageConverter\Services\ImageConverterService;

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
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function convert(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'image' => 'required|image|max:5120', // 5MB in kilobytes
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('image');

        try {
            $downloadUrl = $this->imageConverterService->convert($file);

            return response()->json([
                'status' => 'success',
                'message' => 'Image converted successfully to JPG.',
                'download_url' => url($downloadUrl),
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            \Illuminate\Support\Facades\Log::error('Image conversion failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred during image conversion.',
            ], 500);
        }
    }
}