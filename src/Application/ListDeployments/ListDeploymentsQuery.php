<?php

declare(strict_types=1);

namespace App\Application\ListDeployments;

final class ListDeploymentsQuery
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
     * @var int
     */
    public $limit = 15;
}