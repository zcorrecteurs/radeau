<?php

namespace App\Infrastructure\GitHub;

use App\Domain\Deployment;
use App\Domain\Repository;

final class InMemoryClient implements Client
{
    private $repository;
    private $deployments = [];

    /**
     * Constructor.
     *
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @inheritDoc
     */
    public function getRepository(string $owner, string $name): Repository
    {
        if ($owner !== $this->repository->getOwner() || $name !== $this->repository->getName()) {
            throw new RepositoryNotFoundException($owner, $name);
        }

        return $this->repository;
    }

    /**
     * {@inheritDoc}
     */
    public function readFile(Repository $repository, string $path): string
    {
        $this->checkRepository($repository);
        throw new FileNotFoundException($repository, $path);
    }

    /**
     * {@inheritDoc}
     */
    public function createDeployment(Repository $repository, Deployment $deployment): void
    {
        $this->checkRepository($repository);
        $this->deployments[] = $deployment;
    }

    /**
     * {@inheritDoc}
     */
    public function listDeployments(Repository $repository): array
    {
        $this->checkRepository($repository);
        return $this->deployments;
    }

    private function checkRepository(Repository $repository): void
    {
        if ($repository != $this->repository) {
            throw new \InvalidArgumentException('Wrong repository: ' . $repository);
        }
    }
}