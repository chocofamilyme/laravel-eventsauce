<?php

namespace Chocofamily\LaravelEventSauce\Tests;

use Chocofamily\LaravelEventSauce\SnapshotRepository;
use Chocofamily\LaravelEventSauce\Tests\TestClasses\MoneyAggregateRoot;
use Chocofamily\LaravelEventSauce\Tests\TestClasses\MoneyAggregateRootId;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SnapshotRepositoryTest extends TestCase
{
    use RefreshDatabase;

    /** @var SnapshotRepository */
    protected $snapshotRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->snapshotRepository = $this->app->make(SnapshotRepository::class, ['table' => 'snapshots']);
    }

    public function testItCanPersistToSnapshot()
    {
        $aggregateRootId = MoneyAggregateRootId::create();

        /** @var MoneyAggregateRoot $aggregate */
        $aggregate = $this->repository()->retrieve($aggregateRootId);

        $aggregate->testEvent(7, 700);

        $this->repository()->persist($aggregate);
        $this->repository()->storeSnapshot($aggregate);

        $this->assertDatabaseHas('snapshots', [
            'aggregate_root_id' =>  $aggregateRootId->toString(),
            'state'             =>  json_encode(['userId' => 7, 'amount' => 700]),
        ]);
    }

    public function testItCanRetrieveFromSnapshot()
    {
        $aggregateRootId = MoneyAggregateRootId::create();

        /** @var MoneyAggregateRoot $aggregate */
        $aggregate = $this->repository()->retrieve($aggregateRootId);

        $aggregate->testEvent(7, 700);

        $this->repository()->persist($aggregate);
        $this->repository()->storeSnapshot($aggregate);

        /** @var MoneyAggregateRoot $snapshotAggregate */
        $snapshotAggregate = $this->repository()->retrieveFromSnapshot($aggregateRootId);

        $this->assertInstanceOf(MoneyAggregateRoot::class, $snapshotAggregate);
        $this->assertSame(7, $snapshotAggregate->getUserId());
        $this->assertSame(700, $snapshotAggregate->getAmount());
    }
}
