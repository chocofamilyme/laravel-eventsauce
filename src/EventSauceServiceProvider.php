<?php

namespace Chocofamily\LaravelEventSauce;

use Chocofamily\LaravelEventSauce\Console\GenerateCommand;
use Chocofamily\LaravelEventSauce\Console\MakeAggregateCommand;
use Chocofamily\LaravelEventSauce\Console\MakeConsumerCommand;
use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use Illuminate\Support\ServiceProvider;

final class EventSauceServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/eventsauce.php' => $this->app->configPath('eventsauce.php'),
            ], ['eventsauce', 'eventsauce-config']);

            $this->publishes([
                __DIR__.'/../database/migrations' => $this->app->databasePath('migrations'),
            ], ['eventsauce', 'eventsauce-migrations']);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/eventsauce.php', 'eventsauce');

        $this->app->bind(MessageSerializer::class, function () {
            return new ConstructingMessageSerializer();
        });

        $this->commands([
            GenerateCommand::class,
            MakeAggregateCommand::class,
            MakeConsumerCommand::class,
        ]);
    }

    public function provides()
    {
        return [
            GenerateCommand::class,
            MakeAggregateCommand::class,
            MakeConsumerCommand::class,
        ];
    }
}
