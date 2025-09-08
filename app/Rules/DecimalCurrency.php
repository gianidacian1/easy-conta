<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DecimalCurrency implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!preg_match('/^\d{1,13}(\.\d{1,2})?$/', $value)) {
            $fail("The {$attribute} must be a valid decimal with up to 13 digits and 2 decimal places.");
        }
    }
}