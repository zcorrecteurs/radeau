<?php

namespace App\Tests\Controller;

use App\Infrastructure\Clock\Clock;
use App\Infrastructure\GitHub\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DeploymentControllerTest extends WebTestCase
{
    public function testCreateDeployment()
    {
        Clock::withFrozenTime(\DateTimeImmutable::createFromFormat('U', '0'), function() {
            $client = static::createClient();
            $client->request('POST', '/api/deployment/radeau', [], [], [], '{"ref":"master","environment":"production"}');

            $this->assertEquals(200, $client->getResponse()->getStatusCode());
            $expected = '{"ref":"master","environment":"production","createdAt":"1970-01-01T00:00:00+00:00"}';
            $this->assertJsonStringEqualsJsonString($expected, $client->getResponse()->getContent());

            // Verify that a deployment was actually created.
            $github = self::$container->get(Client::class);
            $repository = $github->getRepository('', '');
            $this->assertEquals(1, count($github->listDeployments($repository)));
        });
    }
}