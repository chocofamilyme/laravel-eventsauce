<?php

namespace Chocofamily\LaravelEventSauce;

use Chocofamily\LaravelEventSauce\Exceptions\AggregateRootRepositoryInstanciationFailed;
use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\AggregateRootRepository as EventSauceAggregateRootRepository;
use EventSauce\EventSourcing\ConstructingAggregateRootRepository;
use EventSauce\EventSourcing\Consumer;
use EventSauce\EventSourcing\DefaultHeadersDecorator;
use EventSauce\EventSourcing\MessageDecorator;
use EventSauce\EventSourcing\MessageDecoratorChain;
use EventSauce\EventSourcing\MessageDispatcherChain;
use EventSauce\EventSourcing\MessageRepository;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

abstract class AggregateRootRepository implements EventSauceAggregateRootRepository
{
    /** @var AggregateRoot|null */
    protected $aggregateRoot;

    /** @var array */
    protected $consumers = [];

    /** @var string */
    protected $connection;

    /** @var string */
    protected $table;

    /** @var MessageRepository */
    protected $messageRepository;

    /** @var array */
    protected $decorators = [];

    /** @var ConstructingAggregateRootRepository */
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

        $this->repository = new ConstructingAggregateRootRepository(
            $this->aggregateRoot,
            $this->getMessageRepository(),
            new MessageDispatcherChain(
                new MessageDispatcher(
                    $this->getConsumerHandlerClass(),
                    $this->getInstanciatedConsumers()
                ),
                new EventMessageDispatcher()
            ),
            new MessageDecoratorChain(
                new DefaultHeadersDecorator(),
                ...$this->getInstanciatedDecorators()
            )
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
     * @return MessageRepository
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function getMessageRepository(): MessageRepository
    {
        $messageRepository = $this->messageRepository ?? config('eventsauce.message_repository');

        return app()->make($messageRepository, [
            'connection'    =>  $this->getConnection(),
            'table'         =>  $this->table
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
     * @return Consumer[]
     */
    protected function getInstanciatedConsumers(): array
    {
        return $this->instanciate($this->consumers);
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