<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDomainMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('domain_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('event_id', 36);
            $table->string('event_type', 100);
            $table->string('aggregate_root_id', 36)->nullable()->index();
            $table->integer('aggregate_root_version')->nullable()->index();
            $table->dateTime('recorded_at', 6)->index();
            $table->jsonb('payload');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('domain_messages');
    }
}
