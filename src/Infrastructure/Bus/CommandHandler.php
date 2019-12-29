<?php

declare(strict_types=1);

namespace App\Infrastructure\Bus;

interface CommandHandler extends Handler
{
    public function handle(object $command): void;
}