<?php

declare(strict_types=1);

namespace App\Application\CreateDeployment;

use App\Domain\Environment;
use Assert\Assertion;
use Assert\AssertionFailedException;

final class CreateDeploymentCommand
{
    private $tenant;
    private $service;
    private $ref;
    private $environment;

    /**
     * Constructor.
     *
     * @param string $tenant
     * @param string $service
     * @param string $ref
     * @param string $environment
     * @throws AssertionFailedException
     */
    public function __construct(string $tenant, string $service, string $ref, string $environment)
    {
        Assertion::notEmpty($tenant, null, 'tenant');
        Assertion::notEmpty($service, null, 'service');
        Assertion::notEmpty($ref, null, 'ref');

        $this->tenant = $tenant;
        $this->service = $service;
        $this->ref = $ref;
        $this->environment = Environment::fromName($environment);
    }

    /**
     * @return string
     */
    public function getTenant(): string
    {
        return $this->tenant;
    }

    /**
     * @return string
     */
    public function getService(): string
    {
        return $this->service;
    }

    /**
     * @return string
     */
    public function getRef(): string
    {
        return $this->ref;
    }

    /**
     * @return Environment
     */
    public function getEnvironment(): Environment
    {
        return $this->environment;
    }
}