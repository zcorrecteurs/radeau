<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GithubController
{
    /**
     * @Route("/webhook/github")
     *
     * @param Request $request
     * @return Response
     */
    public function webhook(Request $request): Response
    {
        return new JsonResponse();
    }
}