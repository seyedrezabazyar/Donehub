# Image Converter Module

A module for converting various image formats to JPG.

## Installation & Setup

1.  **Install required packages:**
    ```bash
    composer require intervention/image
    ```

2.  **Create storage link:**
    ```bash
    php artisan storage:link
    ```

3.  **Publish configuration (optional):**
    ```bash
    php artisan vendor:publish --tag=imageconverter-config
    ```
    This will create a `config/imageconverter.php` file where you can customize settings.

## Usage

### API Endpoint
`POST /api/image/convert`

### Parameters
-   `image` (file, required): The image file to be converted. Supported formats: JPEG, PNG, GIF, BMP, WEBP, TIFF, SVG.

### Example cURL Request
```bash
curl -X POST \
  http://your-domain.com/api/image/convert \
  -H "Accept: application/json" \
  -F "image=@/path/to/your/image.png"
```

### Successful Response (`200 OK`)
```json
{
    "status": "success",
    "message": "Image converted successfully to JPG.",
    "data": {
        "download_url": "http://your-domain.com/storage/converted/your_image_1678886400_a1b2c3d4.jpg",
        "filename": "your_image_1678886400_a1b2c3d4.jpg",
        "size": 123456,
        "size_human": "120.56 KB",
        "original_format": "PNG",
        "original_size": 543210,
        "original_size_human": "530.48 KB",
        "compression_ratio": "77.27%"
    }
}
```

### Error Response (`422 Unprocessable Entity`)
```json
{
    "status": "error",
    "message": "Validation failed.",
    "errors": {
        "image": [
            "Please upload an image file."
        ]
    }
}
```

## Configuration

You can customize the module's behavior by adding the following keys to your `.env` file:

```env
# Quality of the converted JPG image (1-100)
IMAGE_CONVERTER_QUALITY=85

# Maximum upload file size in kilobytes (e.g., 5120 for 5MB)
IMAGE_CONVERTER_MAX_SIZE=5120

# Maximum width for the converted image (in pixels). Leave empty for no limit.
IMAGE_CONVERTER_MAX_WIDTH=2000

# Maximum height for the converted image (in pixels). Leave empty for no limit.
IMAGE_CONVERTER_MAX_HEIGHT=2000

# Number of days to keep converted files before deleting them. Set to 0 to disable cleanup.
IMAGE_CONVERTER_CLEANUP_DAYS=7
```

## Console Command

This module includes a command to clean up old converted images.

```bash
# Clean up files older than the number of days specified in the config
php artisan imageconverter:cleanup

# Clean up files older than a specific number of days
php artisan imageconverter:cleanup --days=30
```

## Testing

To run the module's tests:
```bash
php artisan test --filter ImageConverter
```