<?php

namespace Modules\SlugGenerator\Services;

use Illuminate\Support\Str;

class SlugService
{
    /**
     * Generate a URL-friendly slug from a string.
     *
     * @param string $text
     * @return string
     */
    public function generate(string $text): string
    {
        // Use Laravel's built-in Str::slug helper, which is robust
        // and supports multiple languages, including Persian ('fa').
        return Str::slug($text, '-', 'fa');
    }
}
