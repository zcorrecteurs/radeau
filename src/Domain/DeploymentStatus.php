<?php

declare(strict_types=1);

namespace App\Domain;

final class DeploymentStatus
{
    private $state;
    private $environmentUrl;
    private $logUrl;
    private $createdAt;

    /**
     * Constructor.
     *
     * @param string $state
     * @param string $environmentUrl
     * @param string $logUrl
     * @param \DateTimeImmutable $createdAt
     */
    public function __construct($state, $environmentUrl, $logUrl, \DateTimeImmutable $createdAt = null)
    {
        $this->state = $state;
        $this->environmentUrl = $environmentUrl;
        $this->logUrl = $logUrl;
        $this->createdAt = $createdAt ?: new \DateTimeImmutable();
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @return string
     */
    public function getEnvironmentUrl(): string
    {
        return $this->environmentUrl;
    }

    /**
     * @return string
     */
    public function getLogUrl(): string
    {
        return $this->logUrl;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}