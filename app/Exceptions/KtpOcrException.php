<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class KtpOcrException extends RuntimeException
{
    /** @var array<string, mixed> */
    protected array $context;

    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(string $message, int $code = 500, array $context = [], ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }
}
