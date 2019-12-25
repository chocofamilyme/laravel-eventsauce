<?php

namespace Chocofamily\LaravelEventSauce;

use Chocofamily\LaravelEventSauce\Exceptions\AggregateRootRepositoryInstanciationFailed;
use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\ConstructingAggregateRootRepository;
use EventSauce\EventSourcing\Consumer as EventSauceConsumer;
use EventSauce\EventSourcing\DefaultHeadersDecorator;
use EventSauce\EventSourcing\MessageDecorator;
use EventSauce\EventSourcing\MessageDecoratorChain;
use EventSauce\EventSourcing\MessageDispatcherChain;
use EventSauce\EventSourcing\MessageRepository as EventSauceMessageRepository;
use EventSauce\EventSourcing\Snapshotting\AggregateRootRepositoryWithSnapshotting as EventSauceAggregateRootRepository;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;
use EventSauce\EventSourcing\Snapshotting\ConstructingAggregateRootRepositoryWithSnapshotting;
use EventSauce\EventSourcing\Snapshotting\SnapshotRepository as EventSauceSnapshotRepository;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

abstract class AggregateRootRepository implements EventSauceAggregateRootRepository
{
    /** @var string */
    protected $aggregateRoot;

    /** @var array */
    protected $consumers = [];

    /** @var string */
    protected $connection;

    /** @var string */
    protected $table;

    /** @var string */
    protected $messageRepository;

    /** @var string */
    protected $snapshotRepository;

    /** @var string */
    protected $snapshotTable;

    /** @var array */
    protected $decorators = [];

    /** @var ConstructingAggregateRootRepositoryWithSnapshotting */
    protected $repository;

    /** @var string */
    protected $consumerHandlerClass;

    /**
     * AggregateRootRepository constructor.
     * @throws AggregateRootRepositoryInstanciationFailed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct()
    {
        $this->assertAggregateClassIsValid();

        $aggregateRepository = new ConstructingAggregateRootRepository(
            $this->aggregateRoot,
            $this->getMessageRepository(),
            new MessageDispatcherChain(
                new MessageDispatcher(
                    $this->getConsumerHandlerClass(),
                    $this->consumers
                ),
                new EventMessageDispatcher()
            ),
            new MessageDecoratorChain(
                new DefaultHeadersDecorator(),
                ...$this->getInstanciatedDecorators()
            )
        );

        $this->repository = new ConstructingAggregateRootRepositoryWithSnapshotting(
            $this->aggregateRoot,
            $this->getMessageRepository(),
            $this->getSnapshotRepository(),
            $aggregateRepository
        );
    }

    /**
     * @param AggregateRootId $aggregateRootId
     * @return object
     */
    public function retrieve(AggregateRootId $aggregateRootId): object
    {
        return $this->repository->retrieve($aggregateRootId);
    }

    /**
     * @param object $aggregateRoot
     */
    public function persist(object $aggregateRoot)
    {
        $this->repository->persist($aggregateRoot);
    }

    /**
     * @param AggregateRootId $aggregateRootId
     * @param int $aggregateRootVersion
     * @param object ...$events
     */
    public function persistEvents(AggregateRootId $aggregateRootId, int $aggregateRootVersion, object ...$events)
    {
        $this->repository->persistEvents($aggregateRootId, $aggregateRootVersion, ...$events);
    }

    /**
     * @param AggregateRootId $aggregateRootId
     * @return object
     */
    public function retrieveFromSnapshot(AggregateRootId $aggregateRootId): object
    {
        return $this->repository->retrieveFromSnapshot($aggregateRootId);
    }

    /**
     * @param AggregateRootWithSnapshotting $aggregateRoot
     */
    public function storeSnapshot(AggregateRootWithSnapshotting $aggregateRoot): void
    {
        $this->repository->storeSnapshot($aggregateRoot);
    }

    /**
     * @throws AggregateRootRepositoryInstanciationFailed
     */
    protected function assertAggregateClassIsValid()
    {
        if (is_null($this->aggregateRoot)) {
            throw AggregateRootRepositoryInstanciationFailed::aggregateRootClassDoesNotExist();
        }

        if (! is_a($this->aggregateRoot, AggregateRoot::class, true)) {
            throw AggregateRootRepositoryInstanciationFailed::aggregateRootClassIsNotValid();
        }
    }

    /**
     * @return ConnectionInterface
     */
    protected function getConnection(): ConnectionInterface
    {
        $connection = $this->connection
            ?? config('eventsauce.connection')
            ?? config('database.default');

        return DB::connection($connection);
    }

    /**
     * @return EventSauceMessageRepository
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function getMessageRepository(): EventSauceMessageRepository
    {
        $messageRepository = $this->messageRepository ?? config('eventsauce.message_repository');

        return app()->make($messageRepository, [
            'connection'    =>  $this->getConnection(),
            'table'         =>  $this->table ?? config('eventsauce.table'),
        ]);
    }

    /**
     * @return EventSauceSnapshotRepository
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function getSnapshotRepository(): EventSauceSnapshotRepository
    {
        $snapshotRepository = $this->snapshotRepository ?? config('eventsauce.snapshot_repository');

        return app()->make($snapshotRepository, [
            'connection'    =>  $this->getConnection(),
            'table'         =>  $this->snapshotTable ?? config('eventsauce.snapshot_table'),
        ]);
    }

    /**
     * @return string
     */
    protected function getConsumerHandlerClass(): string
    {
        return $this->consumerHandlerClass ?? config('eventsauce.consumer_handler');
    }

    /**
     * @return MessageDecorator[]
     */
    protected function getInstanciatedDecorators(): array
    {
        return $this->instanciate($this->decorators);
    }

    /**
     * @param array $classes
     * @return array
     */
    protected function instanciate(array $classes): array
    {
        return array_map(function ($class) {
            return is_string($class)
                ? app()->make($class)
                : $class;
        }, $classes);
    }
}
