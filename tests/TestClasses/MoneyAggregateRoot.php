<?php

namespace Chocofamily\LaravelEventSauce\Tests\TestClasses;

use EventSauce\EventSourcing\AggregateRootBehaviour;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;
use EventSauce\EventSourcing\Snapshotting\SnapshottingBehaviour;

class MoneyAggregateRoot implements AggregateRootWithSnapshotting
{
    use AggregateRootBehaviour, SnapshottingBehaviour;

    /** @var int */
    protected $userId;

    /** @var int */
    protected $amount;

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    public function testEvent(int $userId = 1, int $amount = 100)
    {
        $this->recordThat(new MoneyAdded($userId, $amount));
    }

    public function applyMoneyAdded(MoneyAdded $event)
    {
        $this->userId = $event->userId;
        $this->amount = $event->amount;
    }

    protected function createSnapshotState()
    {
        return [
            'userId'    =>  $this->userId,
            'amount'    =>  $this->amount
        ];
    }

    protected static function reconstituteFromSnapshotState(AggregateRootId $id, object $state): AggregateRootWithSnapshotting
    {
        $aggregate = new static($id);
        $aggregate->amount = $state->amount;
        $aggregate->userId = $state->userId;

        return $aggregate;
    }
}