<?php

namespace App\Infrastructure\GitHub;

final class RestClientException extends \RuntimeException implements ClientExceptionInterface
{
    public static function fromException(\Throwable $e)
    {
        return new self($e->getMessage(), $e->getCode());
    }

    public static function fromStatusCode(int $statusCode)
    {
        return new self(sprintf('Unexpected HTTP status code %s', $statusCode), $statusCode);
    }
}