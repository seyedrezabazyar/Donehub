<?php

namespace Modules\Portfolio\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EducationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'institute' => 'required|string|max:255',
            'degree' => 'nullable|string|max:255',
            'field' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];
    }
}
