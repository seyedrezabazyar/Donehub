<?php

namespace Modules\ImageConverter\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ConvertImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Change based on your auth requirements
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'image' => [
                'required',
                'file',
                'mimes:jpeg,jpg,png,gif,bmp,webp,tiff,svg',
                'max:' . config('imageconverter.max_file_size', 5120), // KB
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'image.required' => 'Please upload an image file.',
            'image.file' => 'The uploaded file must be a valid file.',
            'image.mimes' => 'The image must be a file of type: jpeg, jpg, png, gif, bmp, webp, tiff, svg.',
            'image.max' => 'The image size must not exceed ' . (config('imageconverter.max_file_size', 5120) / 1024) . 'MB.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     *
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}