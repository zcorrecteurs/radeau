<?php

namespace App\Domain;

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
    private function __construct(string $name, bool $production, bool $transient)
    {
        $this->name = $name;
        $this->production = $production;
        $this->transient = $transient;
    }

    public static function fromName(string $name): Environment
    {
        switch ($name) {
            case 'prod':
            case 'production':
                return new Environment('production', true, false);
            case 'staging':
                return new Environment('staging', false, false);
            case 'qa':
                return new Environment('qa', false, true);
            default:
                return null;
        }
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