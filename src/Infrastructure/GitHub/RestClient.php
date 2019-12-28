<?php

namespace App\Infrastructure\GitHub;

use App\Domain\Deployment;
use App\Domain\Environment;
use App\Domain\Repository;
use Firebase\JWT\JWT;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Implementation of a GitHub client using the REST API.
 *
 * @see https://developer.github.com/v3/
 */
final class RestClient implements Client
{
    const MACHINE_MAN_MEDIA_TYPE = 'application/vnd.github.machine-man-preview+json';
    const ANT_MAN_MEDIA_TYPE = 'application/vnd.github.ant-man-preview+json';
    const DEFAULT_MEDIA_TYPE = 'application/vnd.github.v3+json';
    const API_URL = 'https://api.github.com';

    private $appId;
    private $privateKey;
    private $cache;
    private $httpClient;

    /**
     * Constructor.
     *
     * @param CacheInterface $appCache
     * @param string $appId
     * @param string $privateKey
     */
    public function __construct(CacheInterface $appCache, string $appId, string $privateKey)
    {
        $this->appId = $appId;
        $this->privateKey = $privateKey;

        $this->cache = $appCache;
        $this->httpClient = HttpClient::create([
            'headers' => [
                'Content-Type' => 'application/json',
                'User-Agent' => 'Radeau-App',
            ],
            // Follow up to 5 redirections.
            'max_redirects' => 5,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getRepository(string $owner, string $name): Repository
    {
        $url = sprintf('%s/repos/%s/%s/installation', self::API_URL, $owner, $name);
        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'Authorization' => $this->getAppAuthorizationHeader(),
                'Accept' => self::MACHINE_MAN_MEDIA_TYPE,
            ],
        ]);
        if ($this->getStatusCode($response) === 404) {
            throw new RepositoryNotFoundException($owner, $name);
        }
        $data = $this->decodeJson($response);

        return new Repository($owner, $name, (int)$data['id']);
    }

    /**
     * {@inheritDoc}
     */
    public function createDeployment(Repository $repository, Deployment $deployment): void
    {
        $url = sprintf('%s/repos/%s/deployments', self::API_URL, $repository->getFullName());
        $body = [
            'ref' => $deployment->getRef(),
            'environment' => $deployment->getEnvironment()->getName(),
            'transient_environment' => $deployment->getEnvironment()->isTransient(),
            'production_environment' => $deployment->getEnvironment()->isProduction(),
        ];
        $response = $this->httpClient->request('POST', $url, [
            'headers' => [
                'Authorization' => $this->getInstallAuthorizationHeader($repository),
                'Accept' => self::ANT_MAN_MEDIA_TYPE,
            ],
            'json' => $body,
        ]);
        $this->checkStatusCode($response);
    }

    /**
     * {@inheritDoc}
     */
    public function listDeployments(Repository $repository): array
    {
        $url = sprintf('%s/repos/%s/deployments', self::API_URL, $repository->getFullName());
        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'Authorization' => $this->getInstallAuthorizationHeader($repository),
                'Accept' => self::ANT_MAN_MEDIA_TYPE,
            ],
        ]);

        $data = $this->decodeJson($response);
        $deployments = [];
        foreach ($data as $obj) {
            $environment = new Environment($obj['environment'], (bool)$obj['production_environment'], (bool)$obj['transient_environment']);
            $createdAt = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s+', $obj['created_at']);
            $deployments[] = new Deployment($obj['ref'], $environment, [], $createdAt);
        }

        return $deployments;
    }

    /**
     * {@inheritDoc}
     */
    public function readFile(Repository $repository, string $path): string
    {
        $url = sprintf('%s/repos/%s/contents/%s', self::API_URL, $repository->getFullName(), $path);
        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'Authorization' => $this->getInstallAuthorizationHeader($repository),
                'Accept' => self::DEFAULT_MEDIA_TYPE,
            ],
        ]);
        if ($this->getStatusCode($response) === 404) {
            throw new FileNotFoundException($repository, $path);
        }

        $data = $this->decodeJson($response);
        if (!isset($data['type']) || $data['type'] !== 'file') {
            // There is something at this path but not a file. We do not even follow symlinks.
            // Directories return an array (and not an object), so do not have any 'type' property.
            throw new FileNotFoundException($repository, $path);
        }
        if ($data['encoding'] !== 'base64') {
            // We only support base64 decoding, but since this field is explicitly specified,
            // maybe other encodings can be used.
            throw new RestClientException('Unexpected file encoding from GitHub API: ' . $data['encoding']);
        }

        return base64_decode($data['content']);
    }

    private function getAppAuthorizationHeader(): string
    {
        return 'Bearer ' . $this->getAppAccessToken();
    }

    private function getAppAccessToken()
    {
        $payload = [
            // Issued at time
            'iat' => time(),
            // JWT expiration time (10 minutes maximum)
            'exp' => time() + 600,
            // GitHub App's identifier
            'iss' => $this->appId,
        ];

        return JWT::encode($payload, $this->privateKey, 'RS256');
    }

    private function getInstallAuthorizationHeader(Repository $repository): string
    {
        return 'token ' . $this->getInstallAccessToken($repository);
    }

    private function getInstallAccessToken(Repository $repository): string
    {
        // Installation access tokens are valid for about an hour, so we avoid recreating one
        // every time but instead reuse them.
        $cacheKey = sprintf('github.%s.token', $repository->getInstallationId());

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($repository) {
            // https://developer.github.com/v3/apps/#create-a-new-installation-token
            $url = sprintf('%s/app/installations/%s/access_tokens', self::API_URL, $repository->getInstallationId());
            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Authorization' => $this->getAppAuthorizationHeader(),
                    'Accept' => self::MACHINE_MAN_MEDIA_TYPE,
                ],
            ]);
            $data = $this->decodeJson($response);

            // Set item's expiration time according to JWT token expiration time. We subtract 1 minute
            // to be on the safe side.
            $expiresAt = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $data['expires_at']);
            $item->expiresAt($expiresAt->sub(new \DateInterval('PT60S')));

            return $data['token'];
        });
    }

    private function checkStatusCode(ResponseInterface $response): void
    {
        $statusCode = $this->getStatusCode($response);
        if ($statusCode >= 300) {
            throw RestClientException::fromStatusCode($statusCode);
        }
    }

    private function getStatusCode(ResponseInterface $response): int
    {
        try {
            return $response->getStatusCode();
        } catch (ExceptionInterface $e) {
            throw RestClientException::fromException($e);
        }
    }

    private function decodeJson(ResponseInterface $response): array
    {
        $this->checkStatusCode($response);
        try {
            return $response->toArray(false);
        } catch (ExceptionInterface $e) {
            throw RestClientException::fromException($e);
        }
    }
}