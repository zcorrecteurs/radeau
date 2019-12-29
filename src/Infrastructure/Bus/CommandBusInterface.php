<?php

declare(strict_types=1);

namespace App\Infrastructure\Bus;

interface CommandBusInterface
{
    public function handle($command): void;
}