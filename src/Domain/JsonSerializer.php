<?php

declare(strict_types=1);

namespace App\Domain;

final class JsonSerializer
{
    public static function decodeDeployment(string $json): Deployment
    {
        $data = json_decode($json, true);
        if (null === $data) {
            throw new \InvalidArgumentException('Invalid JSON: ' . json_last_error_msg());
        }

        $missingKeys = array_diff(['environment', 'ref'], array_keys($data));
        if ($missingKeys) {
            throw new \InvalidArgumentException('Missing keys: ' . implode(', ', $missingKeys));
        }

        $environment = Environment::fromName($data['environment']);

        return new Deployment($data['ref'], $environment);
    }

    public static function encodeDeployment(Deployment $deployment): string
    {
        return json_encode(self::encodeDeploymentAsArray($deployment));
    }

    public static function encodeDeployments(array $deployments): string
    {
        return json_encode(array_map([self::class, 'encodeDeploymentAsArray'], $deployments));
    }

    private static function encodeDeploymentAsArray(Deployment $deployment): array
    {
        return [
            'ref' => $deployment->getRef(),
            'environment' => $deployment->getEnvironment()->getName(),
            'createdAt' => $deployment->getCreatedAt()->format('c'),
        ];
    }

    private function __construct()
    {
        // Do not instantiate.
    }
}