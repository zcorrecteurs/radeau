<?php

declare(strict_types=1);

namespace App\Domain;

final class DeploymentList
{
    private $deployments;

    /**
     * Constructor.
     *
     * @param Deployment[] $deployments
     */
    public function __construct(array $deployments)
    {
        $this->deployments = $deployments;
    }

    public static function empty(): DeploymentList
    {
        return new DeploymentList([]);
    }

    /**
     * @return Deployment[]
     */
    public function getDeployments()
    {
        return $this->deployments;
    }

    public function size(): int
    {
        return count($this->deployments);
    }
}