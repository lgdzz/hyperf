<?php

declare(strict_types=1);

namespace lgdz\hyperf\exception;

use Hyperf\Server\Exception\ServerException;
use Throwable;

class BusinessException extends ServerException
{
    public function __construct(string $message = null, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
