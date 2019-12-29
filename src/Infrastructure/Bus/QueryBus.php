<?php

declare(strict_types=1);

namespace App\Infrastructure\Bus;

final class QueryBus extends AbstractBus implements QueryBusInterface
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
    public function handle(object $query): object
    {
        return $this->resolve($query)->handle($query);
    }
}