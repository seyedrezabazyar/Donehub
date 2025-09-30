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
        Storage::fake('public');
    }

    /**
     * Test successful image conversion.
     */
    public function test_successfully_converts_png_to_jpg()
    {
        $file = UploadedFile::fake()->image('test_image.png', 800, 600)->size(100);

        $response = $this->postJson('/api/image/convert', [
            'image' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'download_url',
                    'filename',
                    'size',
                    'size_human',
                    'original_format',
                    'original_size',
                    'original_size_human',
                    'compression_ratio',
                ]
            ])
            ->assertJson([
                'status' => 'success',
            ]);

        // Verify filename ends with .jpg
        $data = $response->json('data');
        $this->assertStringEndsWith('.jpg', $data['filename']);
    }

    /**
     * Test conversion with different image formats.
     *
     * @dataProvider imageFormatProvider
     */
    public function test_converts_various_image_formats($format)
    {
        $file = UploadedFile::fake()->image("test_image.{$format}")->size(100);

        $response = $this->postJson('/api/image/convert', [
            'image' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);
    }

    public static function imageFormatProvider()
    {
        return [
            'PNG format' => ['png'],
            'GIF format' => ['gif'],
            'BMP format' => ['bmp'],
        ];
    }

    /**
     * Test validation error for missing image.
     */
    public function test_returns_validation_error_if_image_is_missing()
    {
        $response = $this->postJson('/api/image/convert', []);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Validation failed.',
            ])
            ->assertJsonStructure([
                'status',
                'message',
                'errors' => ['image']
            ]);
    }

    /**
     * Test validation error for non-image file.
     */
    public function test_returns_validation_error_for_non_image_file()
    {
        $file = UploadedFile::fake()->create('document.txt', 100, 'text/plain');

        $response = $this->postJson('/api/image/convert', [
            'image' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('image');
    }

    /**
     * Test validation error for oversized image.
     */
    public function test_returns_validation_error_for_oversized_image()
    {
        config(['imageconverter.max_file_size' => 5120]); // 5MB

        $file = UploadedFile::fake()->image('large_image.jpg')->size(6000); // 6MB

        $response = $this->postJson('/api/image/convert', [
            'image' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('image');
    }

    /**
     * Test file storage after conversion.
     */
    public function test_converted_file_is_stored_in_correct_location()
    {
        $file = UploadedFile::fake()->image('test_image.png')->size(100);

        $response = $this->postJson('/api/image/convert', [
            'image' => $file,
        ]);

        $response->assertStatus(200);

        $filename = $response->json('data.filename');
        Storage::disk('public')->assertExists('converted/' . $filename);
    }

    /**
     * Test compression ratio calculation.
     */
    public function test_compression_ratio_is_calculated()
    {
        $file = UploadedFile::fake()->image('test_image.png')->size(500);

        $response = $this->postJson('/api/image/convert', [
            'image' => $file,
        ]);

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertArrayHasKey('compression_ratio', $data);
        $this->assertStringContainsString('%', $data['compression_ratio']);
    }
}