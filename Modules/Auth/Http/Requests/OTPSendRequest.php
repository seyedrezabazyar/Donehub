<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OTPSendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identifier' => 'required|string|max:255',
            'type' => 'nullable|in:sms,email',
            'purpose' => 'nullable|in:login,registration'
        ];
    }

    public function messages(): array
    {
        return [
            'identifier.required' => 'Email or phone number is required',
            'type.in' => 'Type must be either sms or email',
            'purpose.in' => 'Purpose must be either login or registration'
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('type')) {
            $type = filter_var($this->identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'sms';
            $this->merge(['type' => $type]);
        }

        if (!$this->has('purpose')) {
            $this->merge(['purpose' => 'login']);
        }
    }
}
