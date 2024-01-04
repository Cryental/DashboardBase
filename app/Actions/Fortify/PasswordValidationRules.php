<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Rules\Password;

trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    protected function passwordRules(): array
    {
        return ['required', 'string', 'min:8', 'max:255',
            'regex:/^(?=.*[a-zA-Z])(?=.*\d)[^\u{1F600}-\u{1F64F}]*$/u'];
    }
}
