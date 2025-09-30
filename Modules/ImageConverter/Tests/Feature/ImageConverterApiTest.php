<?php

namespace Modules\ImageConverter\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageConverterApiTest extends TestCase
{
    /**
     * Test that an image can be successfully converted to JPG.
     *
     * @return void
     */
    public function test_can_convert_image_to_jpg()
    {
        Storage::fake('local');

        // Create a dummy image file
        $file = UploadedFile::fake()->image('test_image.png');

        $response = $this->postJson('/api/imageconverter/convert', [
            'image' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/jpeg');

        // Check if the response content is a valid image
        $this->assertTrue(@imagecreatefromstring($response->getContent()) !== false);
    }

    /**
     * Test that a validation error is returned if no image is provided.
     *
     * @return void
     */
    public function test_returns_validation_error_for_missing_image()
    {
        $response = $this->postJson('/api/imageconverter/convert', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('image');
    }
}