<?php

declare(strict_types=1);

namespace App\Domain;

use Assert\Assertion;
use Assert\AssertionFailedException;

final class Environment
{
    private $name;
    private $production;
    private $transient;

    /**
     * Constructor.
     *
     * @param string $name
     * @param bool $production
     * @param bool $transient
     */
    public function __construct(string $name, bool $production, bool $transient)
    {
        $this->name = $name;
        $this->production = $production;
        $this->transient = $transient;
    }

    /**
     * @param string $name
     * @return Environment
     * @throws AssertionFailedException
     */
    public static function fromName(string $name): Environment
    {
        $environments = [
            'production' => new Environment('production', true, false),
            'staging' => new Environment('staging', false, false),
            'qa' => new Environment('qa', false, true),
        ];
        Assertion::choice($name, array_keys($environments), null, 'environment');

        return $environments[$name];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isProduction(): bool
    {
        return $this->production;
    }

    /**
     * @return bool
     */
    public function isTransient(): bool
    {
        return $this->transient;
    }
}