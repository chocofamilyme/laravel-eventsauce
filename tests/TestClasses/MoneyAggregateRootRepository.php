<?php

namespace Chocofamily\LaravelEventSauce\Tests\TestClasses;

use Chocofamily\LaravelEventSauce\AggregateRootRepository;

class MoneyAggregateRootRepository extends AggregateRootRepository
{
    protected $aggregateRoot = MoneyAggregateRoot::class;

    protected $consumers = [
        UpdateBalanceTable::class
    ];
}