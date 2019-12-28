<?php

namespace App\Infrastructure\GitHub;

use App\Domain\Deployment;
use App\Domain\Environment;
use App\Domain\Tenant;
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
    public function getTenant(string $account): Tenant
    {
        $response = $this->httpClient->request('GET', self::API_URL . '/app/installations', [
            'headers' => [
                'Authorization' => $this->getAppAuthorizationHeader(),
                'Accept' => self::MACHINE_MAN_MEDIA_TYPE,
            ],
        ]);
        $this->checkStatusCode($response);

        // Note: pagination is not handled here, as we do not expect more than one installation.
        $json = $this->decodeJson($response);
        foreach ($json as $installation) {
            if ($installation['account']['login'] === $account) {
                return new Tenant($installation['account']['login'], (int)$installation['id']);
            }
        }

        throw new TenantNotFoundException($account);
    }

    /**
     * {@inheritDoc}
     */
    public function createDeployment(Tenant $tenant, Deployment $deployment): void
    {
        $url = sprintf('%s/repos/%s/%s/deployments', self::API_URL, $tenant->getAccount(), $deployment->getService());
        $body = [
            'ref' => $deployment->getRef(),
            'environment' => $deployment->getEnvironment()->getName(),
            'transient_environment' => $deployment->getEnvironment()->isTransient(),
            'production_environment' => $deployment->getEnvironment()->isProduction(),
        ];
        $response = $this->httpClient->request('POST', $url, [
            'headers' => [
                'Authorization' => $this->getInstallAuthorizationHeader($tenant),
                'Accept' => self::ANT_MAN_MEDIA_TYPE,
            ],
            'json' => $body,
        ]);
        if ($this->getStatusCode($response) === 404) {
            throw new ServiceNotFoundException($tenant, $deployment->getService());
        }
        $this->checkStatusCode($response);
    }

    /**
     * {@inheritDoc}
     */
    public function listDeployments(Tenant $tenant, string $service): array
    {
        $url = sprintf('%s/repos/%s/%s/deployments', self::API_URL, $tenant->getAccount(), $service);
        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'Authorization' => $this->getInstallAuthorizationHeader($tenant),
                'Accept' => self::ANT_MAN_MEDIA_TYPE,
            ],
        ]);
        if ($this->getStatusCode($response) === 404) {
            throw new ServiceNotFoundException($tenant, $service);
        }
        $this->checkStatusCode($response);
        $data = $this->decodeJson($response);

        $deployments = [];
        foreach ($data as $obj) {
            $environment = new Environment($obj['environment'], (bool)$obj['production_environment'], (bool)$obj['transient_environment']);
            $createdAt = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s+', $obj['created_at']);
            $deployments[] = new Deployment($service, $obj['ref'], $environment, [], $createdAt);
        }

        return $deployments;
    }

    /**
     * {@inheritDoc}
     */
    public function readFile(Tenant $tenant, string $service, string $path): string
    {
        $url = sprintf('%s/repos/%s/%s/contents/%s', self::API_URL, $tenant->getAccount(), $service, $path);
        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'Authorization' => $this->getInstallAuthorizationHeader($tenant),
                'Accept' => self::DEFAULT_MEDIA_TYPE,
            ],
        ]);
        if ($this->getStatusCode($response) === 404) {
            throw new FileNotFoundException($tenant, $service, $path);
        }
        $this->checkStatusCode($response);

        $json = $this->decodeJson($response);
        if (!isset($json['type']) || $json['type'] !== 'file') {
            // There is something at this path but not a file. We do not even follow symlinks.
            // Directories return an array (and not an object), so do not have any 'type' property.
            throw new FileNotFoundException($tenant, $service, $path);
        }
        if ($json['encoding'] !== 'base64') {
            // We only support base64 decoding, but since this field is explicitly specified,
            // maybe other encodings can be used.
            throw new RestClientException('Unexpected file encoding from GitHub API: ' . $json['encoding']);
        }

        return base64_decode($json['content']);
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

    private function getInstallAuthorizationHeader(Tenant $tenant): string
    {
        return 'token ' . $this->getInstallAccessToken($tenant);
    }

    private function getInstallAccessToken(Tenant $tenant): string
    {
        // Installation access tokens are valid for about an hour, so we avoid recreating one
        // every time but instead reuse them.
        $cacheKey = sprintf('github.%s.token', $tenant->getInstallationId());

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($tenant) {
            // https://developer.github.com/v3/apps/#create-a-new-installation-token
            $url = sprintf('%s/app/installations/%s/access_tokens', self::API_URL, $tenant->getInstallationId());
            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Authorization' => $this->getAppAuthorizationHeader(),
                    'Accept' => self::MACHINE_MAN_MEDIA_TYPE,
                ],
            ]);
            $this->checkStatusCode($response);
            $json = $this->decodeJson($response);

            // Set item's expiration time according to JWT token expiration time. We subtract 1 minute
            // to be on the safe side.
            $expiresAt = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $json['expires_at']);
            $item->expiresAt($expiresAt->sub(new \DateInterval('PT60S')));

            return $json['token'];
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
        try {
            return $response->toArray(false);
        } catch (ExceptionInterface $e) {
            throw RestClientException::fromException($e);
        }
    }
}