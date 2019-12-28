<?php

namespace App\Controller;

use App\Domain\JsonSerializer;
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
    public function createDeployment(Request $request): Response
    {
        try {
            $tenant = $this->github->getTenant('zcorrecteurs');
        } catch (TenantNotFoundException $e) {
            return $this->createInvalidArgumentResponse($e->getMessage());
        }
        try {
            $deployment = JsonSerializer::decodeDeployment($request->getContent());
        } catch (\InvalidArgumentException $e) {
            return $this->createInvalidArgumentResponse($e->getMessage());
        }

        try {
            $this->github->createDeployment($tenant, $deployment);
        } catch (ServiceNotFoundException $e) {
            return $this->createInvalidArgumentResponse($e->getMessage());
        }

        return $this->createJsonResponse(JsonSerializer::encodeDeployment($deployment));
    }

    /**
     * @Route("/api/deployment/{service}", methods={"GET"})
     *
     * @param Request $request
     * @param string $service
     * @return Response
     */
    public function listDeployments(Request $request, string $service): Response
    {
        try {
            $tenant = $this->github->getTenant('zcorrecteurs');
        } catch (TenantNotFoundException $e) {
            return $this->createInvalidArgumentResponse($e->getMessage());
        }
        try {
            $deployments = $this->github->listDeployments($tenant, $service);
        } catch (ServiceNotFoundException $e) {
            return $this->createNotFoundResponse($e->getMessage());
        }

        return $this->createJsonResponse( JsonSerializer::encodeDeployments($deployments));
    }

    private function createInvalidArgumentResponse(string $message): Response
    {
        return new JsonResponse([
            'code' => 'INVALID_ARGUMENT',
            'message' => $message,
        ], 400);
    }

    private function createNotFoundResponse(string $message): Response
    {
        return new JsonResponse([
            'code' => 'NOT_FOUND',
            'message' => $message,
        ], 404);
    }

    private function createJsonResponse(string $json): Response
    {
        return new JsonResponse($json, 200, [], true);
    }
}