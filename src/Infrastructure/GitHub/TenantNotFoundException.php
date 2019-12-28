<?php

namespace App\Infrastructure\GitHub;

final class TenantNotFoundException extends \Exception
{
    public function __construct(string $account)
    {
        parent::__construct(sprintf('App is not installed for account "%s"', $account));
    }
}