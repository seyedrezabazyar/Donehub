<?php

namespace Modules\HtmlToPdf\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConvertHtmlRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'file' => 'required|file|mimes:html,htm|max:10240', // Max 10MB
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}