<?php

namespace App\Infrastructure\GitHub;

use App\Domain\Repository;

final class FileNotFoundException extends \Exception
{
    public function __construct(Repository $repository, string $path)
    {
        parent::__construct(sprintf('File "%s" does not exist in repository "%s"', $path, $repository));
    }
}