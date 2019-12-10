<?php

namespace Chocofamily\LaravelEventSauce\Tests\TestClasses;

use Chocofamily\LaravelEventSauce\Consumer;
use EventSauce\EventSourcing\Message;

class MoneyConsumer extends Consumer
{
    public function handleMoneyAdded(MoneyAdded $event, Message $message)
    {

    }
}