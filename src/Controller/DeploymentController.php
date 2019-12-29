<?php

namespace App\Controller;

use App\Application\CreateDeployment\CreateDeploymentCommand;
use App\Application\ListDeployments\ListDeploymentsQuery;
use App\Domain\RepositoryNotFoundException;
use App\Infrastructure\Bus\CommandBusInterface;
use App\Infrastructure\Bus\QueryBusInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class DeploymentController
{
    private $commandBus;
    private $queryBus;
    private $serializer;

    /**
     * Constructor.
     *
     * @param CommandBusInterface $commandBus
     * @param QueryBusInterface $queryBus
     * @param SerializerInterface $serializer
     */
    public function __construct(CommandBusInterface $commandBus, QueryBusInterface $queryBus, SerializerInterface $serializer)
    {
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/api/deployment/{service}", methods={"POST"})
     *
     * @param Request $request
     * @param string $service
     * @return Response
     */
    public function createDeployment(Request $request, string $service): Response
    {
        $json = json_decode($request->getContent(), true);
        if (null === $json) {
            return $this->createInvalidArgumentResponse('Invalid JSON: ' . json_last_error_msg());
        }

        $command = new CreateDeploymentCommand();
        $command->service = $service;
        $command->environment = $json['environment'] ?? null;
        $command->ref = $json['ref'] ?? null;

        try {
            $this->commandBus->handle($command);
        } catch (RepositoryNotFoundException $e) {
            return $this->createNotFoundResponse($e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return $this->createInvalidArgumentResponse($e->getMessage());
        }

        return Response::create('', 202);
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
        $query = new ListDeploymentsQuery();
        $query->service = $service;

        try {
            $result = $this->queryBus->handle($query);
        } catch (RepositoryNotFoundException $e) {
            return $this->createNotFoundResponse($e->getMessage());
        }
        $json = $this->serializer->serialize($result, 'json');

        return $this->createJsonResponse($json);
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