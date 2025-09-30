<?php

namespace Modules\ImageConverter\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageConverterApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Use a fake disk for testing to avoid actual file operations
        Storage::fake('public');
    }

    /**
     * Test successful image conversion.
     *
     * @return void
     */
    public function test_successfully_converts_image()
    {
        // Create a dummy image file
        $file = UploadedFile::fake()->image('test_image.png', 100, 100)->size(100); // 100 KB

        $response = $this->postJson('/api/image/convert', [
            'image' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Image converted successfully.',
        ]);
        $response->assertJsonStructure(['download_url']);

        // Assert that the file was stored
        $data = $response->json();
        $filePath = str_replace(url('/storage'), 'public', $data['download_url']);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $filePath));
    }

    /**
     * Test validation error for missing image file.
     *
     * @return void
     */
    public function test_returns_validation_error_if_image_is_missing()
    {
        $response = $this->postJson('/api/image/convert', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('image');
    }

    /**
     * Test validation error for non-image file type.
     *
     * @return void
     */
    public function test_returns_validation_error_for_non_image_file()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->postJson('/api/image/convert', [
            'image' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('image');
    }

    /**
     * Test validation error for image size exceeding the limit.
     *
     * @return void
     */
    public function test_returns_validation_error_for_oversized_image()
    {
        // Create a dummy image file larger than 5MB (5120 KB)
        $file = UploadedFile::fake()->image('large_image.jpg')->size(6000);

        $response = $this->postJson('/api/image/convert', [
            'image' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('image');
    }
}