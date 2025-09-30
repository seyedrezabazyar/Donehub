<?php

namespace Modules\ImageConverter\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageConverterService
{
    /**
     * Convert the uploaded image to JPEG and store it.
     *
     * @param UploadedFile $file
     * @return string The public URL of the converted file.
     */
    public function convert(UploadedFile $file): string
    {
        // Create a unique filename to avoid collisions
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '_' . time() . '.jpg';
        $storagePath = 'public/converted/' . $filename;

        // Create an image manager instance
        $manager = new ImageManager(new Driver());

        // Read the uploaded image
        $image = $manager->read($file);

        // Encode the image to JPEG format
        $encodedImage = $image->toJpeg();

        // Store the image in the specified path
        Storage::put($storagePath, $encodedImage);

        // Return the public URL for the stored file
        return Storage::url($storagePath);
    }
}