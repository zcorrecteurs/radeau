<?php

declare(strict_types=1);

namespace App\Application\CreateDeployment;

use App\Domain\Deployment;
use App\Infrastructure\Bus\CommandHandler;
use App\Infrastructure\GitHub\Client;

final class CreateDeploymentHandler implements CommandHandler
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
        return CreateDeploymentCommand::class;
    }

    /**
     * @param CreateDeploymentCommand $command
     * @throws \App\Domain\RepositoryNotFoundException
     */
    public function handle($command): void
    {
        $repository = $this->github->getRepository($command->getTenant(), $command->getService());
        $deployment = new Deployment($command->getRef(), $command->getEnvironment());
        $this->github->createDeployment($repository, $deployment);
    }
}