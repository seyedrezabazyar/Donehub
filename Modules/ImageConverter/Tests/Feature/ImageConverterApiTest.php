<?php

namespace Modules\ImageConverter\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageConverterApiTest extends TestCase
{
    /**
     * Test if the API can convert an image to JPG.
     *
     * @return void
     */
    public function test_can_convert_image_to_jpg()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->image('test_image.png');

        $response = $this->postJson('/api/imageconverter/convert', [
            'image' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/jpeg');
        $response->assertHeader('Content-Disposition', 'attachment; filename="test_image.jpg"');
    }

    /**
     * Test API returns validation error if no image is provided.
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