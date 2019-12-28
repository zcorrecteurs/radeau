<?php

namespace App\Tests\Infrastructure\GitHub;

use App\Domain\Tenant;
use App\Infrastructure\GitHub\RestClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * Unit tests for {@link RestClient}.
 */
class RestClientTest extends TestCase
{
    private static $cache;
    private $client;
    private $tenant;

    public static function setUpBeforeClass()
    {
        // This is not ideal, but we keep a persistent cache between tests to avoid getting a
        // new access token every time, and thus avoid hitting the API limit.
        self::$cache = new ArrayAdapter();
    }

    protected function setUp()
    {
        $this->client = new RestClient(self::$cache, getenv('GITHUB_APP_ID'), getenv('GITHUB_PRIVATE_KEY'));
        $this->tenant = new Tenant('zcorrecteurs', (int) getenv('GITHUB_INSTALL_ID'));
    }

    public function testGetTenant()
    {
        $tenant = $this->client->getTenant($this->tenant->getAccount());

        $this->assertEquals($this->tenant, $tenant);
    }

    /**
     * @expectedException \App\Infrastructure\GitHub\TenantNotFoundException
     */
    public function testGetTenantWhenAccountDoesNotExist()
    {
        $this->client->getTenant('foo');
    }

    public function testReadFile()
    {
        $contents = $this->client->readFile($this->tenant, 'radeau', 'bin/console');

        $this->assertStringStartsWith('#!', $contents);
    }

    /**
     * @expectedException \App\Infrastructure\GitHub\FileNotFoundException
     */
    public function testReadFileWhenFileIsADirectory()
    {
        $this->client->readFile($this->tenant, 'radeau', 'bin');
    }

    /**
     * @expectedException \App\Infrastructure\GitHub\FileNotFoundException
     */
    public function testReadFileWhenFileDoesNotExist()
    {
        $this->client->readFile($this->tenant, 'radeau', 'bin/foo');
    }

    /**
     * @expectedException \App\Infrastructure\GitHub\FileNotFoundException
     */
    public function testReadFileWithServiceDoesNotExist()
    {
        $this->client->readFile($this->tenant, 'foo', 'bin/console');
    }
}