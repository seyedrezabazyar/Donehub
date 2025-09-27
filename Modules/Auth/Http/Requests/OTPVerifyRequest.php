<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OTPVerifyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identifier' => 'required|string|max:255',
            'otp' => 'required|string|size:6',
            'purpose' => 'nullable|in:login,registration',
            'name' => 'required_if:purpose,registration|nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'national_id' => 'nullable|string|max:20'
        ];
    }

    public function messages(): array
    {
        return [
            'identifier.required' => 'Email or phone number is required',
            'otp.required' => 'OTP code is required',
            'otp.size' => 'OTP code must be exactly 6 digits',
            'purpose.in' => 'Purpose must be either login or registration',
            'name.required_if' => 'Name is required for registration',
            'email.unique' => 'This email is already registered',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('purpose')) {
            $this->merge(['purpose' => 'login']);
        }

        if ($this->has('phone') && $this->phone) {
            $phoneService = app(\Modules\Auth\Services\PhoneService::class);

            if ($phoneService->isValid($this->phone)) {
                $this->merge([
                    'phone' => $phoneService->normalize($this->phone)
                ]);
            }
        }
    }
}
