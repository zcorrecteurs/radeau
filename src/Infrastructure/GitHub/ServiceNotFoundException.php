<?php

namespace App\Infrastructure\GitHub;

use App\Domain\Tenant;

final class ServiceNotFoundException extends \Exception
{
    public function __construct(Tenant $tenant, string $service)
    {
        parent::__construct(sprintf('Service "%s" does not exist for tenant "%s"', $service, $tenant->getAccount()));
    }
}