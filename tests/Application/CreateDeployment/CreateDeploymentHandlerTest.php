<?php

namespace App\Tests\Controller;

use App\Application\CreateDeployment\CreateDeploymentCommand;
use App\Application\CreateDeployment\CreateDeploymentHandler;
use App\Domain\Deployment;
use App\Domain\Repository;
use App\Infrastructure\GitHub\InMemoryClient;
use PHPUnit\Framework\TestCase;

class CreateDeploymentHandlerTest extends TestCase
{
    const OWNER = 'zcorrecteurs';
    private $github;
    private $handler;

    public function setUp()
    {
        $this->github = new InMemoryClient(self::OWNER);
        $this->handler = new CreateDeploymentHandler($this->github);
    }

    public function testHandle()
    {
        $command = new CreateDeploymentCommand();
        $command->ref = 'master';
        $command->service = 'test';
        $command->environment = 'staging';

        $this->handler->handle($command);

        $deployments = $this->github->listDeployments(new Repository(self::OWNER, 'test', 1), 100);
        $this->assertEquals(1, $deployments->size());

        /** @var Deployment $deployment */
        $deployment = $deployments->getDeployments()[0];
        $this->assertEquals('master', $deployment->getRef());
        $this->assertEquals('staging', $deployment->getEnvironment()->getName());
    }
}