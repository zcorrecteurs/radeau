<?php

declare(strict_types=1);

namespace App\Domain;

final class Deployment
{
    private $ref;
    private $environment;
    private $statuses;
    private $createdAt;

    /**
     * Constructor.
     *
     * @param string $ref
     * @param Environment $environment
     * @param DeploymentStatus[] $statuses
     * @param \DateTimeImmutable $createdAt
     */
    public function __construct(
        string $ref,
        Environment $environment,
        array $statuses = [],
        \DateTimeImmutable $createdAt = null)
    {
        $this->ref = $ref;
        $this->environment = $environment;
        $this->statuses = $statuses;
        $this->createdAt = $createdAt ?: new \DateTimeImmutable();
    }

    /**
     * @return string
     */
    public function getRef(): string
    {
        return $this->ref;
    }

    /**
     * @return Environment
     */
    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    /**
     * @return DeploymentStatus[]|array
     */
    public function getStatuses()
    {
        return $this->statuses;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}