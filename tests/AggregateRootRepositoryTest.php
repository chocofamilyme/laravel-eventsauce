<?php

namespace Chocofamily\LaravelEventSauce\Tests;

use Chocofamily\LaravelEventSauce\AggregateRootRepository;
use Chocofamily\LaravelEventSauce\Exceptions\AggregateRootRepositoryInstanciationFailed;
use Chocofamily\LaravelEventSauce\Tests\TestClasses\MoneyAggregateRoot;
use Chocofamily\LaravelEventSauce\Tests\TestClasses\MoneyAggregateRootId;
use Chocofamily\LaravelEventSauce\Tests\TestClasses\MoneyAggregateRootRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class AggregateRootRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Schema::create('balance', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->integer('balance');
            $table->timestamps();
        });
    }

    public function testItThrowsAggregateRootRepositoryInstanciationFailedException()
    {
        $this->expectException(AggregateRootRepositoryInstanciationFailed::class);
        $this->expectExceptionMessage("You have to set an aggregate root before the repository can be initialized.");

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
            'aggregate_root_id' =>  $aggregateRootId->toString()
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
            'balance'   =>  150
        ]);

        $this->assertDatabaseHas('balance', [
            'user_id'   =>  2,
            'balance'   =>  500
        ]);
    }

    private function repository(): MoneyAggregateRootRepository
    {
        return new MoneyAggregateRootRepository();
    }
}