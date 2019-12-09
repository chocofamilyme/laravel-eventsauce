<?php

namespace Chocofamily\LaravelEventSauce;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\Snapshot;
use EventSauce\EventSourcing\Snapshotting\SnapshotRepository as EventSauceSnapshotRepository;
use Illuminate\Database\Connection;
use Illuminate\Support\Carbon;

class SnapshotRepository implements EventSauceSnapshotRepository
{
    /** @var Connection  */
    protected $connection;

    /** @var string  */
    protected $table;

    public function __construct(Connection $connection, string $table)
    {
        $this->connection = $connection;

        $this->table = $table;
    }

    public function persist(Snapshot $snapshot): void
    {
        $this->connection
            ->table($this->table)
            ->insert([
                'aggregate_root_id'         =>  $snapshot->aggregateRootId()->toString(),
                'aggregate_root_version'    =>  $snapshot->aggregateRootVersion(),
                'state'                     =>  $snapshot->state(),
                'recorded_at'               =>  Carbon::now()->toDateTimeString()
            ]);
    }

    public function retrieve(AggregateRootId $id): ?Snapshot
    {
        $snapshot = $this->connection
            ->table($this->table)
            ->where('aggregate_root_id', $id->toString())
            ->first();

        if (is_null($snapshot)) {
            return null;
        }

        return new Snapshot(
            AggregateRootId::fromString($snapshot->aggregate_root_id),
            $snapshot->aggregate_root_version,
            $snapshot->state
        );
    }
}