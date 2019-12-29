<?php

declare(strict_types=1);

namespace App\Infrastructure\Bus;

interface Handler
{
    public function supports(): string;

    public function handle(object $request);
}