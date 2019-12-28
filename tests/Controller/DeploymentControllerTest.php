<?php

namespace App\Tests\Controller;

use App\Controller\DeploymentController;
use App\Domain\Repository;
use App\Infrastructure\GitHub\InMemoryClient;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DeploymentControllerTest extends WebTestCase
{
    private $repository;
    private $github;
    private $controller;

    public function setUp()
    {
        $this->repository = new Repository('zcorrecteurs', 'radeau', 1);
        $this->github = new InMemoryClient($this->repository);
        $this->controller = new DeploymentController($this->github);
    }

    public function testCreateDeployment()
    {
        $client = static::createClient();
        $client->request('POST', '/api/deployment/radeau', [], [], [], '{"ref":"master","environment":"production"}');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString('{"ref":"master","environment":"production"}', $client->getResponse()->getContent());

        $this->assertEquals(1, count($this->github->listDeployments($this->repository)));
    }
}