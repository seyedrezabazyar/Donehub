<?php

namespace Modules\PasswordGenerator\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GeneratePasswordRequest extends FormRequest
{
    /**
     * تعیین اینکه آیا کاربر مجاز به این درخواست است یا نه
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * قوانین اعتبارسنجی
     */
    public function rules(): array
    {
        return [
            'length' => 'required|integer|min:1|max:50',
            'include_numbers' => 'sometimes|boolean',
            'include_lowercase' => 'sometimes|boolean',
            'include_uppercase' => 'sometimes|boolean',
            'include_symbols' => 'sometimes|boolean',
        ];
    }

    /**
     * پیام‌های خطای سفارشی
     */
    public function messages(): array
    {
        return [
            'length.required' => 'طول رمز عبور الزامی است',
            'length.integer' => 'طول رمز عبور باید عدد باشد',
            'length.min' => 'طول رمز عبور حداقل باید 1 باشد',
            'length.max' => 'طول رمز عبور حداکثر می‌تواند 50 باشد',
            'include_numbers.boolean' => 'مقدار include_numbers باید true یا false باشد',
            'include_lowercase.boolean' => 'مقدار include_lowercase باید true یا false باشد',
            'include_uppercase.boolean' => 'مقدار include_uppercase باید true یا false باشد',
            'include_symbols.boolean' => 'مقدار include_symbols باید true یا false باشد',
        ];
    }

    /**
     * اعتبارسنجی اضافی بعد از validation اولیه
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $includeNumbers = $this->input('include_numbers', false);
            $includeLowercase = $this->input('include_lowercase', false);
            $includeUppercase = $this->input('include_uppercase', false);
            $includeSymbols = $this->input('include_symbols', false);

            // چک کنیم حداقل یکی انتخاب شده باشه
            if (!$includeNumbers && !$includeLowercase && !$includeUppercase && !$includeSymbols) {
                $validator->errors()->add(
                    'options',
                    'حداقل یکی از گزینه‌های include_numbers، include_lowercase، include_uppercase یا include_symbols باید true باشد'
                );
            }
        });
    }

    /**
     * هندل کردن خطاهای validation
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'خطا در اعتبارسنجی داده‌ها',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}