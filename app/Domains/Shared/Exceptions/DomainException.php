<?php

declare(strict_types=1);

namespace App\Domains\Shared\Exceptions;

class DomainException extends \Exception
{
    public function __construct(string $message = 'Erro de domínio', int $code = 422, ?\Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
