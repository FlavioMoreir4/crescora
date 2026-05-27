<?php

declare(strict_types=1);

namespace App\Domains\Shared\Exceptions;

final class NotFoundException extends DomainException
{
    public function __construct(string $message = 'Recurso não encontrado', ?\Exception $previous = null)
    {
        parent::__construct($message, 404, $previous);
    }
}
