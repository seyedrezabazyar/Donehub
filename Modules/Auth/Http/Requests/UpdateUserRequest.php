<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = $this->route('id');
        
        return [
            'name' => 'sometimes|string|max:255',
            'username' => [
                'sometimes',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9_]+$/',
                Rule::unique('users')->ignore($userId)
            ],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId)
            ],
            'phone' => [
                'sometimes',
                'string',
                'regex:/^(\+98|0)?9\d{9}$/',
                Rule::unique('users')->ignore($userId)
            ],
            'password' => 'sometimes|string|min:8|confirmed',
            'preferred_method' => 'sometimes|in:email,phone',
            'avatar' => 'sometimes|nullable|string|max:255'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'نام الزامی است',
            'name.max' => 'نام نباید بیشتر از 255 کاراکتر باشد',
            'username.unique' => 'این نام کاربری قبلاً استفاده شده است',
            'username.regex' => 'نام کاربری فقط می‌تواند شامل حروف، اعداد و _ باشد',
            'email.email' => 'فرمت ایمیل معتبر نیست',
            'email.unique' => 'این ایمیل قبلاً ثبت شده است',
            'phone.regex' => 'فرمت شماره تلفن معتبر نیست',
            'phone.unique' => 'این شماره تلفن قبلاً ثبت شده است',
            'password.min' => 'رمز عبور باید حداقل 8 کاراکتر باشد',
            'password.confirmed' => 'تأیید رمز عبور مطابقت ندارد',
            'preferred_method.in' => 'روش ترجیحی باید email یا phone باشد'
        ];
    }
}