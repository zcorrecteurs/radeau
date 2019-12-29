<?php

declare(strict_types=1);

namespace App\Application\CreateDeployment;

use App\Domain\Deployment;
use App\Domain\Environment;
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
        if (!$command->tenant) {
            throw new \InvalidArgumentException('You must provide a non-empty "tenant"');
        }
        if (!$command->ref) {
            throw new \InvalidArgumentException('You must provide a non-empty "ref"');
        }
        if (!$command->environment) {
            throw new \InvalidArgumentException('You must provide a non-empty "environment"');
        }
        $repository = $this->github->getRepository($command->tenant, $command->service);

        $environment = Environment::fromName($command->environment);
        $deployment = new Deployment($command->ref, $environment);
        $this->github->createDeployment($repository, $deployment);
    }
}