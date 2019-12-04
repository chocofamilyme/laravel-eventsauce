<?php

namespace Chocofamily\LaravelEventSauce;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDispatcher;

final class EventMessageDispatcher implements MessageDispatcher
{
    public function dispatch(Message ...$messages)
    {
        foreach ($messages as $message) {
            event($message->event());
        }
    }
}
