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
     * @param string $owner
     * @param string $name
     */
    public function __construct(string $owner, string $name)
    {
        $this->repository = new Repository($owner, $name, 1);
    }

    /**
     * @inheritDoc
     */
    public function getRepository(string $owner, string $name): Repository
    {
        // In this implementation (and only this one), we allow passing empty parameters
        // to allow retrieving the single repository this client is about.
        if (($owner && $owner !== $this->repository->getOwner())
            || ($name && $name !== $this->repository->getName())) {
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
    public function listDeployments(Repository $repository, int $limit = self::DEFAULT_LIMIT): array
    {
        $this->checkRepository($repository);

        return array_slice(array_reverse($this->deployments), 0, $limit);
    }

    private function checkRepository(Repository $repository): void
    {
        if ($repository != $this->repository) {
            throw new \InvalidArgumentException('Wrong repository: ' . $repository);
        }
    }
}