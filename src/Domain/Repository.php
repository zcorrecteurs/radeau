<?php

declare(strict_types=1);

namespace App\Domain;

final class Repository
{
    private $owner;
    private $name;
    private $installationId;

    /**
     * Constructor.
     *
     * @param string $owner
     * @param string $name
     * @param int $installationId
     */
    public function __construct(string $owner, string $name, int $installationId)
    {
        $this->owner = $owner;
        $this->name = $name;
        $this->installationId = $installationId;
    }

    /**
     * @return string
     */
    public function getOwner(): string
    {
        return $this->owner;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return sprintf('%s/%s', $this->owner, $this->name);
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
        return $this->getFullName();
    }
}