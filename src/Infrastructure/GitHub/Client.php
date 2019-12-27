<?php

namespace App\Infrastructure\GitHub;

use App\Domain\Deployment;

interface Client
{
    public function createDeployment(Deployment $deployment);
}