<?php

namespace Chocofamily\LaravelEventSauce\Tests\TestClasses;

use EventSauce\EventSourcing\AggregateRootId;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class MoneyAggregateRootId implements AggregateRootId
{
    /** @var string */
    private $identifier;

    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    public function toString(): string
    {
        return $this->identifier;
    }

    public function toUuid(): UuidInterface
    {
        return Uuid::fromString($this->identifier);
    }

    public static function create(): self
    {
        return new static(Uuid::uuid4()->toString());
    }

    public static function fromString(string $aggregateRootId): AggregateRootId
    {
        return new static($aggregateRootId);
    }
}