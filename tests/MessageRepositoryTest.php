<?php

namespace Chocofamily\LaravelEventSauce\Tests;

use Chocofamily\LaravelEventSauce\MessageRepository;
use Chocofamily\LaravelEventSauce\Tests\TestClasses\MoneyAdded;
use Chocofamily\LaravelEventSauce\Tests\TestClasses\MoneyAggregateRootId;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MessageRepositoryTest extends TestCase
{
    use RefreshDatabase;

    /** @var MessageRepository  */
    protected $messageRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->messageRepository = $this->app->make(MessageRepository::class, ['table' => 'domain_messages']);
    }

    public function testItCanPersistMessage()
    {
       $aggregateId = MoneyAggregateRootId::create();

       $message = $this->getTestMessage($aggregateId);

       $this->messageRepository->persist($message);

       $this->assertDatabaseHas('domain_messages', [
           'id'                 =>  1,
           'aggregate_root_id'  =>  $aggregateId->toString()
       ]);
    }

    public function testItCanRetrieveMessages()
    {
        $aggregateId = MoneyAggregateRootId::create();

        $message = $this->getTestMessage($aggregateId);

        $this->messageRepository->persist($message);

        foreach ($this->messageRepository->retrieveAll($aggregateId) as $message) {
            $this->assertEquals($aggregateId, $message->aggregateRootId());
            $this->assertInstanceOf(MoneyAdded::class, $message->event());
        }
    }
}