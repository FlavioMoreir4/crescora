<?php

declare(strict_types=1);

namespace App\Domains\Shared\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class PhoneRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = preg_replace('/\D/', '', (string) $value);
        $length = strlen($value);

        if ($length < 10 || $length > 11) {
            $fail('O :attribute deve conter 10 ou 11 dígitos (DDD + telefone).');

            return;
        }

        if ($length === 11 && $value[2] !== '9') {
            $fail('O :attribute não é um telefone celular válido (9º dígito).');
        }
    }
}
