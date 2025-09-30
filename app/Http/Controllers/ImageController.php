<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function convertToJpg(Request $request)
    {
        $request->validate([
            'image' => 'required|image',
        ]);

        // Create an image manager instance with the GD driver
        $manager = new ImageManager(new Driver());

        // Read the uploaded image
        $image = $manager->read($request->file('image'));

        // Encode the image to JPEG format with 75% quality
        $encoded = $image->toJpeg(75);

        // Create a response with the image data
        $response = Response::make($encoded);

        // Set the content type header to image/jpeg
        $response->header('Content-Type', 'image/jpeg');

        return $response;
    }
}