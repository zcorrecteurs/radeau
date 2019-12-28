<?php

namespace App\Infrastructure\GitHub;

use App\Domain\Deployment;
use App\Domain\Tenant;

/**
 * A client to interact with GitHub.
 */
interface Client
{
    /**
     * Returns the tenant associated with the given account name.
     *
     * @param string $account Account name. It can be either an organization or a user.
     * @return Tenant
     * @throws TenantNotFoundException If there is no account with the specified name for which the app is installed.
     */
    public function getTenant(string $account): Tenant;

    /**
     * Returns the content of a file inside a repository.
     *
     * @param Tenant $tenant
     * @param string $service Service name.
     * @param string $path Path to the file in the service's repository.
     * @return string
     * @throws FileNotFoundException If there is no file at specified path, or if is is not a file.
     */
    public function readFile(Tenant $tenant, string $service, string $path): string;

    /**
     * Creates a new deployment.
     *
     * @param Tenant $tenant
     * @param Deployment $deployment
     */
    public function createDeployment(Tenant $tenant, Deployment $deployment): void;
}