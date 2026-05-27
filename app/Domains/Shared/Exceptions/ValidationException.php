<?php

declare(strict_types=1);

namespace App\Domains\Shared\Exceptions;

final class ValidationException extends DomainException
{
    private array $errors;

    public function __construct(string $message = 'Dados inválidos', array $errors = [], ?\Exception $previous = null)
    {
        $this->errors = $errors;

        parent::__construct($message, 422, $previous);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
