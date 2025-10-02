<?php

namespace Modules\ImageConverter\Tests\Unit;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\ImageConverter\Services\ImageConverterService;

class ImageConverterServiceTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->service = new ImageConverterService();
    }

    public function test_convert_method_returns_array_with_required_keys()
    {
        $file = UploadedFile::fake()->image('test.png');

        $result = $this->service->convert($file);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('url', $result);
        $this->assertArrayHasKey('size', $result);
        $this->assertArrayHasKey('size_human', $result);
    }

    public function test_converted_filename_ends_with_jpg()
    {
        $file = UploadedFile::fake()->image('test.png');

        $result = $this->service->convert($file);

        $this->assertStringEndsWith('.jpg', $result['filename']);
    }

    public function test_file_is_stored_after_conversion()
    {
        $file = UploadedFile::fake()->image('test.png');

        $result = $this->service->convert($file);

        $storagePath = 'converted/' . $result['filename'];
        Storage::disk('public')->assertExists($storagePath);
    }
}