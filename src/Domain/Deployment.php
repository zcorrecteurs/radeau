<?php

namespace App\Domain;

final class Deployment
{
    private $service;
    private $ref;
    private $environment;

    /**
     * Constructor.
     *
     * @param string $service
     * @param string $ref
     * @param Environment $environment
     */
    public function __construct(string $service, string $ref, Environment $environment)
    {
        $this->service = $service;
        $this->ref = $ref;
        $this->environment = $environment;
    }

    /**
     * @return string
     */
    public function getService(): string
    {
        return $this->service;
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
}