<?php

namespace Chocofamily\LaravelEventSauce;

use EventSauce\EventSourcing\Consumer as EventSauceConsumer;
use EventSauce\EventSourcing\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class ConsumerHandler implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    /** @var string */
    private $consumer;

    /** @var Message[] */
    private $messages = [];

    /**
     * ConsumerHandler constructor.
     * @param string $consumer
     * @param Message[] $messages
     */
    public function __construct(string $consumer, Message ...$messages)
    {
        $this->consumer = $consumer;
        $this->messages = $messages;
    }

    public function handle(Container $container): void
    {
        /** @var EventSauceConsumer $consumer */
        $consumer = $container->make($this->consumer);

        foreach ($this->messages as $message) {
            $consumer->handle($message);
        }
    }
}
