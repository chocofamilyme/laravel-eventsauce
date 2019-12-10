<?php

namespace Chocofamily\LaravelEventSauce\Tests\TestClasses;

use EventSauce\EventSourcing\Serialization\SerializablePayload;

class MoneyAdded implements SerializablePayload
{
    /** @var int */
    public $amount;

    /**
     * TestEvent constructor.
     * @param int $amount
     */
    public function __construct(int $amount)
    {
        $this->amount = $amount;
    }


    public function toPayload(): array
    {
        return [
            'amount'    =>  $this->amount
        ];
    }

    public static function fromPayload(array $payload): SerializablePayload
    {
        return new self(
            (int)$payload['amount']
        );
    }
}