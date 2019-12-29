<?php

namespace App\Infrastructure\Clock;

final class Clock
{
    private static $now;

    public static function now(): \DateTimeImmutable
    {
        if (isset(self::$now)) {
            return self::$now;
        }

        return new \DateTimeImmutable();
    }

    public static function withFrozenTime(\DateTimeImmutable $now, callable $fn)
    {
        try {
            self::$now = $now;
            return $fn();
        } finally {
            self::$now = null;
        }
    }
}