<?php

namespace Chocofamily\LaravelEventSauce\Tests\TestClasses;

use EventSauce\EventSourcing\Serialization\SerializablePayload;

class MoneyAdded implements SerializablePayload
{
    /** @var int */
    public $userId;

    /** @var int */
    public $amount;

    /**
     * TestEvent constructor.
     * @param int $userId
     * @param int $amount
     */
    public function __construct(int $userId, int $amount)
    {
        $this->userId = $userId;
        $this->amount = $amount;
    }


    public function toPayload(): array
    {
        return [
            'userId'    =>  $this->userId,
            'amount'    =>  $this->amount
        ];
    }

    public static function fromPayload(array $payload): SerializablePayload
    {
        return new self(
            (int)$payload['userId'],
            (int)$payload['amount']
        );
    }
}