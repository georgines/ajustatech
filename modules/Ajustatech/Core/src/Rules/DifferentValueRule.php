<?php

namespace Ajustatech\Core\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DifferentValueRule implements ValidationRule
{
    public function __construct(private readonly mixed $referenceValue)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ((string) $value === (string) $this->referenceValue) {
            $fail('O valor de :attribute deve ser diferente da origem.');
        }
    }
}

