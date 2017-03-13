<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCinemaTicketClassTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cinema_ticket_class', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cinema_id')->unsigned();
            $table->string('name');
            $table->integer('price');
            $table->tinyInteger('start_day');
            $table->tinyInteger('end_day');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cinema_ticket_class');
    }
}
