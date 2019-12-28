<?php

namespace App\Infrastructure\GitHub;

use App\Domain\Deployment;
use App\Domain\Tenant;

final class InMemoryClient implements Client
{
    private $tenant;
    private $deployments = [];

    /**
     * Constructor.
     *
     * @param string $account
     */
    public function __construct(string $account)
    {
        $this->tenant = new Tenant($account, 1);
    }

    /**
     * @inheritDoc
     */
    public function getTenant(string $account): Tenant
    {
        if ($account !== $this->tenant->getAccount()) {
            throw new TenantNotFoundException($account);
        }

        return $this->tenant;
    }

    /**
     * {@inheritDoc}
     */
    public function readFile(Tenant $tenant, string $service, string $path): string
    {
        $this->checkTenant($tenant);
        throw new FileNotFoundException($tenant, $service, $path);
    }

    /**
     * {@inheritDoc}
     */
    public function createDeployment(Tenant $tenant, Deployment $deployment): void
    {
        $this->checkTenant($tenant);
        $this->deployments[] = $deployment;
    }

    /**
     * @return Deployment[]
     */
    public function getDeployments(): array
    {
        return $this->deployments;
    }

    private function checkTenant(Tenant $tenant): void
    {
        if ($tenant != $this->tenant) {
            throw new \InvalidArgumentException('Wrong tenant: ' . $tenant);
        }
    }
}