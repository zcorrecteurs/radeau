<?php

namespace App\Infrastructure\GitHub;

use App\Domain\Deployment;
use App\Domain\DeploymentList;
use App\Domain\Repository;
use App\Domain\RepositoryNotFoundException;

final class InMemoryClient implements Client
{
    private $owner;
    private $deployments = [];

    /**
     * Constructor.
     *
     * @param string $owner
     */
    public function __construct(string $owner)
    {
        $this->owner = $owner;
    }

    /**
     * @inheritDoc
     */
    public function getRepository(string $owner, string $name): Repository
    {
        if ($owner !== $this->owner) {
            throw new RepositoryNotFoundException($owner, $name);
        }

        // Otherwise, we consider any repository to exist.
        return new Repository($owner, $name, 1);
    }

    /**
     * {@inheritDoc}
     */
    public function readFile(Repository $repository, string $path): string
    {
        throw new FileNotFoundException($repository, $path);
    }

    /**
     * {@inheritDoc}
     */
    public function createDeployment(Repository $repository, Deployment $deployment): void
    {
        if ($repository->getOwner() !== $this->owner) {
            return;
        }
        if (!isset($this->deployments[$repository->getName()])) {
            $this->deployments[$repository->getName()] = [];
        }
        array_unshift($this->deployments[$repository->getName()], $deployment);
    }

    /**
     * {@inheritDoc}
     */
    public function listDeployments(Repository $repository, int $limit): DeploymentList
    {
        if ($repository->getOwner() !== $this->owner) {
            return DeploymentList::empty();
        }
        $deployments = $this->deployments[$repository->getName()] ?? [];

        return new DeploymentList(array_slice($deployments, 0, $limit));
    }
}