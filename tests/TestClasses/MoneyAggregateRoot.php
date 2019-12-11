<?php

namespace Chocofamily\LaravelEventSauce\Tests\TestClasses;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootBehaviour;

class MoneyAggregateRoot implements AggregateRoot
{
    use AggregateRootBehaviour;

    protected $amount;

    public function testEvent(int $userId = 1, int $amount = 100)
    {
        $this->recordThat(new MoneyAdded($userId, $amount));
    }

    public function applyMoneyAdded(MoneyAdded $event)
    {
        $this->amount = $event->amount;
    }
}