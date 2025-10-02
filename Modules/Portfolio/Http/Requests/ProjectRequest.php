<?php

namespace Modules\Portfolio\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'link' => 'nullable|url|max:255',
            'image' => 'nullable|string|max:255',
        ];
    }
}
