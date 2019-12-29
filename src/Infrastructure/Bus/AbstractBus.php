<?php

declare(strict_types=1);

namespace App\Infrastructure\Bus;

abstract class AbstractBus
{
    /**
     * @var Handler[]
     */
    private $handlers;

    /**
     * Constructor.
     *
     * @param iterable $handlers
     */
    public function __construct(iterable $handlers)
    {
        $this->handlers = [];
        foreach ($handlers as $handler) {
            /** @var Handler $handler */
            $this->handlers[$handler->supports()] = $handler;
        }
    }

    protected final function resolve(object $request): Handler
    {
        $type = get_class($request);
        $handler = $this->handlers[$type] ?? null;
        if (null === $handler) {
            throw new HandlerNotFoundException($type);
        }

        return $handler;
    }
}