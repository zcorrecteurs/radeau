<?php

namespace App\Controller;

use App\Domain\Deployment;
use App\Domain\Environment;
use App\Infrastructure\GitHub\Client;
use App\Infrastructure\GitHub\ServiceNotFoundException;
use App\Infrastructure\GitHub\TenantNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DeploymentController
{
    private $github;

    /**
     * Constructor.
     *
     * @param Client $github
     */
    public function __construct(Client $github)
    {
        $this->github = $github;
    }

    /**
     * @Route("/api/deployment", methods={"POST"})
     *
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        try {
            $tenant = $this->github->getTenant('zcorrecteurs');
        } catch (TenantNotFoundException $e) {
            return $this->createInvalidArgumentResponse($e->getMessage());
        }

        $json = json_decode($request->getContent(), true);
        if (null === $json) {
            return $this->createInvalidArgumentResponse('Invalid JSON content: ' . json_last_error_msg());
        }

        $missingKeys = array_diff(['environment', 'service', 'ref'], array_keys($json));
        if ($missingKeys) {
            return $this->createInvalidArgumentResponse('Missing keys: ' . implode(', ', $missingKeys));
        }

        try {
            $environment = Environment::fromName($json['environment']);
        } catch (\InvalidArgumentException $e) {
            return $this->createInvalidArgumentResponse($e->getMessage());
        }

        $deployment = new Deployment($json['service'], $json['ref'], $environment);
        try {
            $this->github->createDeployment($tenant, $deployment);
        } catch (ServiceNotFoundException $e) {
            return $this->createInvalidArgumentResponse($e->getMessage());
        }

        return new JsonResponse([
            'ref' => $deployment->getRef(),
            'service' => $deployment->getService(),
            'environment' => $deployment->getEnvironment()->getName(),
        ]);
    }

    private function createInvalidArgumentResponse(string $message)
    {
        return new JsonResponse([
            'code' => 'INVALID_ARGUMENT',
            'message' => $message,
        ], 400);
    }
}