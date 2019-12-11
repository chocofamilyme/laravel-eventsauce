<?php

namespace Chocofamily\LaravelEventSauce;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDispatcher as EventSauceMessageDispatcher;

final class EventMessageDispatcher implements EventSauceMessageDispatcher
{
    public function dispatch(Message ...$messages)
    {
        foreach ($messages as $message) {
            event($message->event());
        }
    }
}
