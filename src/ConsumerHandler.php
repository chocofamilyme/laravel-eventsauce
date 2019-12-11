<?php

namespace Chocofamily\LaravelEventSauce;

use EventSauce\EventSourcing\Consumer;
use EventSauce\EventSourcing\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class ConsumerHandler implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    /** @var Consumer */
    private $consumer;

    /** @var Message[] */
    private $messages = [];

    /**
     * ConsumerHandler constructor.
     * @param Consumer $consumer
     * @param Message[] $messages
     */
    public function __construct(Consumer $consumer, Message ...$messages)
    {
        $this->consumer = $consumer;
        $this->messages = $messages;
    }

    public function handle(): void
    {
        foreach ($this->messages as $message) {
            $this->consumer->handle($message);
        }
    }
}
