<?php

declare(strict_types=1);

namespace App\Domains\Shared\Exceptions;

final class TenantMismatchException extends DomainException
{
    public function __construct(string $message = 'Recurso não pertence ao time atual', ?\Exception $previous = null)
    {
        parent::__construct($message, 403, $previous);
    }
}
