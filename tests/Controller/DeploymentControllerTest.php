<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DeploymentControllerTest extends WebTestCase
{
    public function testCreateDeployment()
    {
        $client = static::createClient();
        $client->request('POST', '/api/deployment/test', [], [], [], '{"ref":"master","environment":"production"}');
        $this->assertEquals(202, $client->getResponse()->getStatusCode());
    }
}