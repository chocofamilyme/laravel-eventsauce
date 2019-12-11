<?php

namespace Chocofamily\LaravelEventSauce\Tests;

use Chocofamily\LaravelEventSauce\AggregateRootRepository;
use Chocofamily\LaravelEventSauce\Exceptions\AggregateRootRepositoryInstanciationFailed;
use Chocofamily\LaravelEventSauce\Tests\TestClasses\MoneyAggregateRoot;
use Chocofamily\LaravelEventSauce\Tests\TestClasses\MoneyAggregateRootId;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AggregateRootRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function testItThrowsAggregateRootRepositoryInstanciationFailedException()
    {
        $this->expectException(AggregateRootRepositoryInstanciationFailed::class);
        $this->expectExceptionMessage('You have to set an aggregate root before the repository can be initialized.');

        new class() extends AggregateRootRepository {
            protected $aggregateRoot = null;
        };
    }

    public function testItCanRetrieveAggregateRoot()
    {
        $aggregateRootId = MoneyAggregateRootId::create();

        /** @var MoneyAggregateRoot $aggregateRoot */
        $aggregateRoot = $this->repository()->retrieve($aggregateRootId);

        $this->assertInstanceOf(MoneyAggregateRoot::class, $aggregateRoot);
        $this->assertSame($aggregateRootId, $aggregateRoot->aggregateRootId());
    }

    public function testItCanPersistAnAggregateRoot()
    {
        $aggregateRootId = MoneyAggregateRootId::create();

        $aggregateRoot = $this->repository()->retrieve($aggregateRootId);

        $aggregateRoot->testEvent(2, 122);

        $this->repository()->persist($aggregateRoot);

        $this->assertDatabaseHas('domain_messages', [
            'aggregate_root_id' =>  $aggregateRootId->toString(),
        ]);
    }

    public function testItCanDispatchItsConsumers()
    {
        $aggregateRootId = MoneyAggregateRootId::create();

        /** @var MoneyAggregateRoot $aggregateRoot */
        $aggregateRoot = $this->repository()->retrieve($aggregateRootId);

        $aggregateRoot->testEvent(1, 100);
        $aggregateRoot->testEvent(1, 50);
        $aggregateRoot->testEvent(2, 500);

        $this->repository()->persist($aggregateRoot);

        $this->assertDatabaseHas('balance', [
            'user_id'   =>  1,
            'balance'   =>  150,
        ]);

        $this->assertDatabaseHas('balance', [
            'user_id'   =>  2,
            'balance'   =>  500,
        ]);
    }
}
