<?php

declare(strict_types=1);

namespace App\Infrastructure\Bus;

final class SynchronousCommandBus extends AbstractBus implements CommandBusInterface
{
    /**
     * Constructor.
     *
     * @param iterable $handlers
     */
    public function __construct(iterable $handlers)
    {
        parent::__construct($handlers);
    }

    /**
     * {@inheritDoc}
     */
    public function handle($command): void
    {
        $this->resolve($command)->handle($command);
    }
}