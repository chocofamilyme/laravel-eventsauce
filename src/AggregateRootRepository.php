<?php

namespace Chocofamily\LaravelEventSauce;

use Chocofamily\LaravelEventSauce\Exceptions\AggregateRootRepositoryInstanciationFailed;
use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootRepository as EventSauceAggregateRootRepository;
use EventSauce\EventSourcing\ConstructingAggregateRootRepository;
use EventSauce\EventSourcing\DefaultHeadersDecorator;
use EventSauce\EventSourcing\MessageDecoratorChain;
use EventSauce\EventSourcing\MessageDispatcherChain;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use EventSauce\EventSourcing\Consumer;

abstract class AggregateRootRepository implements EventSauceAggregateRootRepository
{
    /** @var AggregateRoot|null */
    protected $aggregateRoot;

    /** @var array */
    protected $consumers = [];

    /** @var array */
    protected $queuedConsumers = [];

    /** @var ConnectionInterface */
    protected $connection;

    /** @var string */
    protected $table;

    /** @var MessageRepository */
    protected $messageRepository;

    /** @var array */
    protected $decorators = [];

    /** @var ConstructingAggregateRootRepository */
    protected $repository;

    /** @var Container */
    protected $container;

    /**
     * AggregateRootRepository constructor.
     * @param Container $container
     * @throws AggregateRootRepositoryInstanciationFailed
     */
    public function __construct(Container $container)
    {
        $this->assertAggregateClassIsValid();

        $this->container = $container;

        $this->repository = new ConstructingAggregateRootRepository(
            $this->aggregateRoot,
            $this->getMessageRepository(),
            new MessageDispatcherChain(
                new SynchronousMessageDispatcher(...$this->getInstanciatedConsumers())
            ),
            new MessageDecoratorChain(
                new DefaultHeadersDecorator(),
                ...$this->getInstanciatedDecorators()
            )
        );
    }

    /**
     * @throws AggregateRootRepositoryInstanciationFailed
     */
    protected function assertAggregateClassIsValid()
    {
        if (is_null($this->aggregateRoot)) {
            throw AggregateRootRepositoryInstanciationFailed::aggregateRootClassDoesNotExist();
        }

        if(! is_a($this->aggregateRoot, AggregateRoot::class, true)) {
            throw AggregateRootRepositoryInstanciationFailed::aggregateRootClassIsNotValid();
        }
    }

    protected function getConnection(): ConnectionInterface
    {
        $connection = $this->connection
            ?? config('eventsauce.connection')
            ?? config('database.default');

        return DB::connection($connection);
    }

    protected function getMessageRepository(): MessageRepository
    {
        $messageRepository = $this->messageRepository ?? config('eventsauce.message_repository');

        return $this->container->makeWith($messageRepository, [
            'connection'    =>  $this->getConnection(),
            'table'         =>  $this->table
        ]);
    }

    protected function getInstanciatedConsumers(): array
    {
        return $this->instanciate($this->consumers);
    }

    protected function getInstanciatedDecorators(): array
    {
        return $this->instanciate($this->decorators);
    }

    protected function instanciate(array $classes): array
    {
        return array_map(function ($class) {
            return is_string($class)
                ? $this->container->make($class)
                : $class;
        }, $classes);
    }
}