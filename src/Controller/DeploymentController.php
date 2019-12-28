<?php

namespace App\Controller;

use App\Domain\Deployment;
use App\Domain\Environment;
use App\Infrastructure\GitHub\Client;
use App\Infrastructure\GitHub\TenantNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
            throw new BadRequestHttpException('Unknown account: zcorrecteurs');
        }

        $json = json_decode($request->getContent(), true);
        if (null === $json) {
            throw new BadRequestHttpException('Invalid JSON content');
        }

        $missingKeys = array_diff(['environment', 'service', 'ref'], array_keys($json));
        if ($missingKeys) {
            throw new BadRequestHttpException('Missing keys: ' . implode(', ', $missingKeys));
        }

        $environment = Environment::fromName($json['environment']);
        if (null === $environment) {
            throw new BadRequestHttpException('Unknown environment: ' . $json['environment']);
        }

        $deployment = new Deployment($json['service'], $json['ref'], $environment);
        $this->github->createDeployment($tenant, $deployment);

        return new JsonResponse([
            'ref' => $deployment->getRef(),
            'service' => $deployment->getService(),
            'environment' => $deployment->getEnvironment()->getName(),
        ]);
    }
}