<?php

namespace Chocofamily\LaravelEventSauce\Tests;

use Chocofamily\LaravelEventSauce\ConsumerHandler;
use Chocofamily\LaravelEventSauce\MessageDispatcher;
use Chocofamily\LaravelEventSauce\Tests\TestClasses\UpdateBalanceTable;
use Illuminate\Support\Facades\Bus;

class MessageDispatcherTest extends TestCase
{
    public function testItCanDispatchMessages()
    {
        $message = $this->getTestMessage();

        Bus::fake();

        $this->dispatcher()->dispatch($message);

        Bus::assertDispatched(ConsumerHandler::class);
    }

    private function dispatcher()
    {
        return new MessageDispatcher(
            ConsumerHandler::class,
            [new UpdateBalanceTable()]
        );
    }
}