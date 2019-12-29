<?php

declare(strict_types=1);

namespace App\Infrastructure\Bus;

final class HandlerNotFoundException extends \RuntimeException
{
    public function __construct(string $type) {
        parent::__construct(sprintf('No handler found for type "%s"', $type));
    }
}