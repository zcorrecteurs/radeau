<?php

declare(strict_types=1);

namespace App\Infrastructure\Bus;

interface QueryBusInterface
{
    public function handle(object $query): object;
}