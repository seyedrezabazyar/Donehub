<?php

namespace Modules\SlugGenerator\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SlugGeneratorController extends Controller
{
    /**
     * Store a newly created slug in storage.
     */
    public function store(Request $request)
    {
        // گرفتن متن ورودی
        $text = $request->input('text');

        // گرفتن پارامتر اختیاری max_length برای محدود کردن طول اسلاگ
        $maxLength = $request->input('max_length');

        // بررسی اینکه text وجود داره یا خالی نیست
        if (!$text) {
            return response()->json([
                'error' => 'text field is required'
            ], 400);
        }

        // تبدیل متن به slug
        $slug = Str::slug($text);

        // اگر max_length داده شده باشه و عددی باشه، اسلاگ کوتاه شود
        if ($maxLength && is_numeric($maxLength)) {
            $slug = substr($slug, 0, (int)$maxLength);
        }

        // بازگرداندن پاسخ JSON
        return response()->json([
            'original_text' => $text,
            'slug' => $slug
        ]);
    }
}
