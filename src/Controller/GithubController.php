<?php

namespace App\Controller;

use App\Infrastructure\Kubernetes\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GithubController
{
    private $kubernetes;

    /**
     * Constructor.
     *
     * @param Client $kubernetes
     */
    public function __construct(Client $kubernetes)
    {
        $this->kubernetes = $kubernetes;
    }

    /**
     * @Route("/webhook/github", methods={"POST"})
     *
     * @param Request $request
     * @return Response
     */
    public function webhook(Request $request): Response
    {
        $type = $request->headers->get('X-GitHub-Event');
        $json = json_decode($request->getContent(), true);
        var_dump('type', $type);
        var_dump('json', $json);
        switch ($type) {
            case 'deployment_event':
                $deployment = $json['deployment'];
                break;
            default:
                // Nothing to do.
        }
        return new Response();
    }
}