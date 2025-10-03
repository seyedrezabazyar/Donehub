SlugGenerator Module Documentation (with Max Length Option)
1️⃣ Summary of Changes

store method in SlugGeneratorController.php has been updated:

Accepts input text (text) from the request.

Added an optional max_length parameter to limit the length of the generated slug.

Generates slug using Str::slug($text).

If max_length is provided, the slug is truncated to the specified length.

Returns a JSON response with:

{
    "original_text": "input text",
    "slug": "generated slug"
}

2️⃣ File Paths

Controller:
Modules/SlugGenerator/Http/Controllers/SlugGeneratorController.php

API Route:
Modules/SlugGenerator/routes/api.php
Example route registration:

use Illuminate\Support\Facades\Route;
use Modules\SlugGenerator\Http\Controllers\SlugGeneratorController;

Route::prefix('v1')->group(function () {
    Route::post('/sluggenerators', [SlugGeneratorController::class, 'store']);
});

3️⃣ How to Run

Make sure the Laravel server is running:

php artisan serve


Usually available at http://127.0.0.1:8000.

The API endpoint for POST requests:

http://127.0.0.1:8000/api/v1/sluggenerators

4️⃣ Request Parameters (Body)

text (required): The input text to be converted to a slug

max_length (optional): Maximum number of characters for the output slug

Example JSON body:

{
    "text": "This is a very long text example",
    "max_length": 10
}

5️⃣ Sample JSON Responses

With max_length:

{
    "original_text": "This is a very long text example",
    "slug": "this-is-a-"
}


Without max_length:

{
    "original_text": "This is a very long text example",
    "slug": "this-is-a-very-long-text-example"
}


If text is missing:

{
    "error": "text field is required"
}

6️⃣ Tools for Testing

Postman (recommended)

Method: POST

URL: http://127.0.0.1:8000/api/v1/sluggenerators

Headers:

Content-Type: application/json
Accept: application/json


Body → raw → JSON

cURL (Terminal):

curl -X POST http://127.0.0.1:8000/api/v1/sluggenerators \
-H "Content-Type: application/json" \
-H "Accept: application/json" \
-d '{"text":"This is a very long text example","max_length":10}'

7️⃣ Important Notes

If max_length is greater than the actual slug length, the full slug will be returned.

If the input text is shorter than max_length, the full slug will be returned.

The original input text (original_text) is always included in the JSON response.