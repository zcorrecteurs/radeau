<?php

namespace App\Infrastructure\GitHub;

final class RepositoryNotFoundException extends \Exception
{
    public function __construct(string $owner, string $name)
    {
        parent::__construct(sprintf('Repository "%s/%s" does not exist', $owner, $name));
    }
}