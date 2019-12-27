<?php

namespace App\Infrastructure\GitHub;

use App\Domain\Deployment;
use Firebase\JWT\JWT;
use Symfony\Component\HttpClient\HttpClient as SymfonyHttpClient;

final class HttpClient implements Client
{
    private $appId;
    private $privateKey;
    private $httpClient;

    /**
     * Constructor.
     *
     * @param string $appId
     * @param string $privateKey
     */
    public function __construct(string $appId, string $privateKey)
    {
        $this->appId = $appId;
        $this->privateKey = $privateKey;
        $this->httpClient = SymfonyHttpClient::create([
            'headers' => [
                'Accept' => self::getAcceptHeader(['machine-man', 'ant-man', 'flash']),
                'Content-Type' => 'application/json',
                'User-Agent' => 'Radeau',
            ],
        ]);
    }

    public function createDeployment(Deployment $deployment)
    {
        $url = sprintf('https://api.github.com/repos/zcorrecteurs/%s/deployments', $deployment->getService());
        $body = [
            'ref' => $deployment->getRef(),
            'environment' => $deployment->getEnvironment()->getName(),
            'transient_environment' => $deployment->getEnvironment()->isTransient(),
            'production_environment' => $deployment->getEnvironment()->isProduction(),
        ];
        $this->httpClient->request('POST', $url, [
            'headers' => [
                'Authorization' => $this->getAuthorizationHeader(),
            ],
            'json' => $body,
        ]);
    }

    private function getAuthorizationHeader(): string
    {
        $payload = [
            // Issued at time
            'iat' => time(),
            // JWT expiration time (10 minutes maximum)
            'exp' => time() + 600,
            // GitHub App's identifier
            'iss' => $this->appId,
        ];
        return 'Bearer ' . JWT::encode($payload, $this->privateKey, 'RS256');
    }

    private static function getAcceptHeader(array $previews = []): string
    {
        $accept = ['application/vnd.github.v3+json'];
        foreach ($previews as $preview) {
            $accept[] = sprintf('application/vnd.github.%s-preview+json', $preview);
        }
        return implode(', ', $accept);
    }
}