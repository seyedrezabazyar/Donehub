<?php

namespace Modules\ImageConverter\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManagerStatic as Image;

class ConvertController extends Controller
{
    /**
     * Converts the uploaded image to JPG format.
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function convert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $file = $request->file('image');
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $newName = $originalName . '.jpg';

        $image = Image::make($file)->encode('jpg', 80);

        return response($image, 200)
            ->header('Content-Type', 'image/jpeg')
            ->header('Content-Disposition', 'attachment; filename="' . $newName . '"');
    }
}