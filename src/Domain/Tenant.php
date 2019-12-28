<?php

namespace App\Domain;

final class Tenant
{
    private $account;
    private $installationId;

    /**
     * Constructor.
     *
     * @param string $account
     * @param int $installationId
     */
    public function __construct(string $account, int $installationId)
    {
        $this->account = $account;
        $this->installationId = $installationId;
    }

    /**
     * @return string
     */
    public function getAccount(): string
    {
        return $this->account;
    }

    /**
     * @return int
     */
    public function getInstallationId(): int
    {
        return $this->installationId;
    }

    public function __toString()
    {
        return sprintf('%s (%s)', $this->account, $this->installationId);
    }
}