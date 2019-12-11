<?php

namespace Chocofamily\LaravelEventSauce;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDispatcher as EventSauceMessageDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;

final class MessageDispatcher implements EventSauceMessageDispatcher
{
    /** @var array */
    private $consumers;

    /** @var string */
    private $handlerClass;

    public function __construct(string $handlerClass, array $consumers)
    {
        $this->handlerClass = $handlerClass;

        $this->consumers = $consumers;
    }

    public function dispatch(Message ...$messages)
    {
        foreach ($this->consumers as $consumer) {
            if (is_a($consumer, ShouldQueue::class, true)) {
                dispatch(new $this->handlerClass($consumer, ...$messages));
            } else {
                dispatch_now(new $this->handlerClass($consumer, ...$messages));
            }
        }
    }
}
