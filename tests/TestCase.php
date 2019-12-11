<?php

namespace Chocofamily\LaravelEventSauce\Tests;

use Chocofamily\LaravelEventSauce\EventSauceServiceProvider;
use Chocofamily\LaravelEventSauce\Tests\TestClasses\MoneyAdded;
use Chocofamily\LaravelEventSauce\Tests\TestClasses\MoneyAggregateRootId;
use EventSauce\EventSourcing\DefaultHeadersDecorator;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

       $this->loadMigrationsFrom([
           '--database' =>  'testing',
           '--path'     =>  realpath(__DIR__ . '/../database/migrations')
       ]);
    }

    protected function getPackageProviders($app)
    {
        return [EventSauceServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
    }

    protected function getTestMessage(MoneyAggregateRootId $id = null): Message
    {
        $event = new MoneyAdded(1, 100);

        $id = $id ?? MoneyAggregateRootId::create();

        $decorator = new DefaultHeadersDecorator();

        return $decorator->decorate(new Message($event, [Header::AGGREGATE_ROOT_ID => $id]));
    }

}