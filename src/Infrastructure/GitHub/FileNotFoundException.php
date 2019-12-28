<?php

namespace App\Infrastructure\GitHub;

use App\Domain\Tenant;

final class FileNotFoundException extends \Exception
{
    public function __construct(Tenant $tenant, string $service, string $path)
    {
        parent::__construct(sprintf('File "%s" does not exist for service %s/%s', $path, $tenant->getAccount(), $service));
    }
}