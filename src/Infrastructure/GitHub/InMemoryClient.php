<?php

namespace App\Infrastructure\GitHub;

use App\Domain\Deployment;

final class InMemoryClient implements Client
{
    private $deployments = [];

    /**
     * {@inheritDoc}
     */
    public function createDeployment(Deployment $deployment)
    {
        $this->deployments[] = $deployment;
    }

    /**
     * @return Deployment[]
     */
    public function getDeployments(): array
    {
        return $this->deployments;
    }
}