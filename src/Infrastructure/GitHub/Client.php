<?php

namespace App\Infrastructure\GitHub;

use App\Domain\Deployment;
use App\Domain\Repository;

/**
 * A client to interact with GitHub.
 */
interface Client
{
    const DEFAULT_LIMIT = 15;

    /**
     * Returns the repository with the given name.
     *
     * @param string $owner Owner account name, who can be either an organization or a user.
     * @param string $name Repository name, scoped under the owner.
     * @return Repository
     * @throws RepositoryNotFoundException If the app is not installed for the owner, or if the repository does not exist.
     */
    public function getRepository(string $owner, string $name): Repository;

    /**
     * Creates a new deployment.
     *
     * @param Repository $repository
     * @param Deployment $deployment
     * @throws RepositoryNotFoundException If the given repository does not exist.
     */
    public function createDeployment(Repository $repository, Deployment $deployment): void;

    /**
     * @param Repository $repository
     * @param int $limit
     * @return Deployment[]
     * @throws RepositoryNotFoundException If the given repository does not exist.
     */
    public function listDeployments(Repository $repository, int $limit = self::DEFAULT_LIMIT): array;

    /**
     * Returns the content of a file inside a repository.
     *
     * @param Repository $repository
     * @param string $path Path to the file in the service's repository.
     * @return string
     * @throws RepositoryNotFoundException If the given repository does not exist.
     * @throws FileNotFoundException If there is no file at specified path, or if is is not a file.
     */
    public function readFile(Repository $repository, string $path): string;
}