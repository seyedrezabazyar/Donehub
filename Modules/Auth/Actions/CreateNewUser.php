<?php

namespace Modules\Auth\Actions;

use Modules\Auth\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Illuminate\Validation\Rules\Password;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param array<string, string> $input
     * @return User
     */
    public function create(array $input): User
    {
        // Determine if this is OTP-based registration (no password provided)
        $isOTPRegistration = empty($input['password']) &&
            (isset($input['email_verified_at']) || isset($input['phone_verified_at']));

        // Build validation rules
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9_]+$/',
                'unique:users'
            ],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                'unique:users'
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'unique:users'
            ],
            'national_id' => ['nullable', 'string', 'max:20'],
            'preferred_method' => ['nullable', 'in:password,otp'],
        ];

        // Add password rules only if not OTP registration
        if (!$isOTPRegistration) {
            $rules['password'] = array_merge(
                ['required'],
                $this->passwordRules()
            );
            $rules['password_confirmation'] = ['required_with:password'];
        }

        // Validate input
        Validator::make($input, $rules)->validate();

        // Ensure at least email or phone is provided
        if (empty($input['email']) && empty($input['phone'])) {
            throw new \InvalidArgumentException('Either email or phone number must be provided');
        }

        // Create user
        $userData = [
            'name' => $input['name'],
            'username' => $input['username'] ?? null, // Will be auto-generated in model
            'email' => $input['email'] ?? null,
            'phone' => $input['phone'] ?? null,
            'national_id' => $input['national_id'] ?? null,
            'preferred_method' => $input['preferred_method'] ?? ($isOTPRegistration ? 'otp' : 'password'),
            'email_verified_at' => $input['email_verified_at'] ?? null,
            'phone_verified_at' => $input['phone_verified_at'] ?? null,
        ];

        // Add password only if provided
        if (!empty($input['password'])) {
            $userData['password'] = Hash::make($input['password']);
        }

        return User::create($userData);
    }
}
