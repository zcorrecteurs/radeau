<?php

declare(strict_types=1);

namespace App\Application\CreateDeployment;

final class CreateDeploymentCommand
{
    /**
     * @var string
     */
    public $tenant = 'zcorrecteurs';

    /**
     * @var string
     */
    public $service;

    /**
     * @var string
     */
    public $ref;

    /**
     * @var string
     */
    public $environment;
}