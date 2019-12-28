<?php

namespace App\Tests\Infrastructure\GitHub;

use App\Domain\Repository;
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
    private $repository;

    public static function setUpBeforeClass()
    {
        // This is not ideal, but we keep a persistent cache between tests to avoid getting a
        // new access token every time, and thus avoid hitting the API limit.
        self::$cache = new ArrayAdapter();
    }

    protected function setUp()
    {
        $this->client = new RestClient(self::$cache, getenv('GITHUB_APP_ID'), getenv('GITHUB_PRIVATE_KEY'));
        $this->repository = new Repository('zcorrecteurs', 'radeau', (int)getenv('GITHUB_INSTALL_ID'));
    }

    public function testGetRepository()
    {
        $repository = $this->client->getRepository($this->repository->getOwner(), $this->repository->getName());

        $this->assertEquals($this->repository, $repository);
    }

    /**
     * @expectedException \App\Infrastructure\GitHub\RepositoryNotFoundException
     */
    public function testGetRepositoryWhenOwnerDoesNotExist()
    {
        $this->client->getRepository('foo', $this->repository->getName());
    }

    /**
     * @expectedException \App\Infrastructure\GitHub\RepositoryNotFoundException
     */
    public function testGetRepositoryWhenRepositoryDoesNotExist()
    {
        $this->client->getRepository($this->repository->getOwner(), 'foo');
    }

    public function testReadFile()
    {
        $contents = $this->client->readFile($this->repository, 'bin/console');

        $this->assertStringStartsWith('#!', $contents);
    }

    /**
     * @expectedException \App\Infrastructure\GitHub\FileNotFoundException
     */
    public function testReadFileWhenFileIsADirectory()
    {
        $this->client->readFile($this->repository, 'bin');
    }

    /**
     * @expectedException \App\Infrastructure\GitHub\FileNotFoundException
     */
    public function testReadFileWhenFileDoesNotExist()
    {
        $this->client->readFile($this->repository, 'bin/foo');
    }
}