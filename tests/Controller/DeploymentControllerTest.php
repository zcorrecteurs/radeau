<?php

namespace App\Tests\Controller;

use App\Controller\DeploymentController;
use App\Infrastructure\GitHub\InMemoryClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class DeploymentControllerTest extends TestCase
{
    private $client;
    private $controller;

    public function setUp()
    {
        $this->client = new InMemoryClient('zcorrecteurs');
        $this->controller = new DeploymentController($this->client);
    }

    public function testCreate()
    {
        $request = Request::create('/api/deployment', 'POST', [], [], [], [], '{"ref":"master","service":"foo","environment":"production"}');

        $response = $this->controller->create($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString('{"ref":"master","service":"foo","environment":"production"}', $response->getContent());

        $this->assertEquals(1, count($this->client->getDeployments()));
    }
}