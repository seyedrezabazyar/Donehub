<?php

namespace Modules\Auth\Actions;

use Modules\Auth\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;
    
    /**
     * Validate and reset the user's forgotten password.
     *
     * @param User $user
     * @param array<string, string> $input
     * @return void
     */
    public function reset(User $user, array $input): void
    {
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();
        
        $user->forceFill([
            'password' => Hash::make($input['password']),
            'failed_attempts' => 0,
            'locked_until' => null,
        ])->save();
        
        // Revoke all existing tokens for security
        $user->tokens()->delete();
    }
}