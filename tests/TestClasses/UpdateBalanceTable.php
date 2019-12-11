<?php

namespace Chocofamily\LaravelEventSauce\Tests\TestClasses;

use Chocofamily\LaravelEventSauce\Consumer;
use EventSauce\EventSourcing\Message;
use Illuminate\Support\Facades\DB;

final class UpdateBalanceTable extends Consumer
{
    public function onMoneyAdded(MoneyAdded $event, Message $message)
    {
        $query = DB::table('balance')->where('user_id', $event->userId);

        if ($query->exists()) {
            $query->increment('balance', $event->amount);
        } else {
            $query->insert([
                'user_id'   =>  $event->userId,
                'balance'   =>  $event->amount
            ]);
        }
    }
}