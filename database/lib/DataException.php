<?php

declare(strict_types=1);

namespace KlinikaID\Data;

use RuntimeException;

final class DataException extends RuntimeException
{
    public function __construct(public readonly string $errorCode, string $message)
    {
        parent::__construct($message);
    }
}
