<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Image Quality
    |--------------------------------------------------------------------------
    |
    | Set the quality of the converted JPG images (1-100)
    | Higher values mean better quality but larger file size
    |
    */
    'quality' => env('IMAGE_CONVERTER_QUALITY', 85),

    /*
    |--------------------------------------------------------------------------
    | Maximum File Size
    |--------------------------------------------------------------------------
    |
    | Maximum upload file size in kilobytes (KB)
    |
    */
    'max_file_size' => env('IMAGE_CONVERTER_MAX_SIZE', 5120), // 5MB

    /*
    |--------------------------------------------------------------------------
    | Maximum Image Dimensions
    |--------------------------------------------------------------------------
    |
    | Set maximum width and height for converted images
    | Set to null for no limit
    |
    */
    'max_width' => env('IMAGE_CONVERTER_MAX_WIDTH', null),
    'max_height' => env('IMAGE_CONVERTER_MAX_HEIGHT', null),

    /*
    |--------------------------------------------------------------------------
    | Cleanup Old Files
    |--------------------------------------------------------------------------
    |
    | Automatically delete files older than specified days
    | Set to 0 to disable automatic cleanup
    |
    */
    'cleanup_days' => env('IMAGE_CONVERTER_CLEANUP_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Storage Path
    |--------------------------------------------------------------------------
    |
    | Path where converted images will be stored
    |
    */
    'storage_path' => 'public/converted',
];