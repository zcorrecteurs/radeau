<?php

declare(strict_types=1);

namespace App\Application\ListDeployments;

use App\Domain\DeploymentList;
use App\Infrastructure\Bus\QueryHandler;
use App\Infrastructure\GitHub\Client;

final class ListDeploymentsHandler implements QueryHandler
{
    private $github;

    /**
     * Constructor.
     *
     * @param Client $github
     */
    public function __construct(Client $github)
    {
        $this->github = $github;
    }

    public function supports(): string
    {
        return ListDeploymentsQuery::class;
    }

    /**
     * @param ListDeploymentsQuery $query
     * @return DeploymentList
     * @throws \App\Domain\RepositoryNotFoundException
     */
    public function handle($query): DeploymentList
    {
        $repository = $this->github->getRepository($query->tenant, $query->service);

        return $this->github->listDeployments($repository, $query->limit);
    }
}