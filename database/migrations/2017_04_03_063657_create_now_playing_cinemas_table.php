<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNowPlayingCinemasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('now_playing_cinemas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('movie');
            $table->string('name');
            $table->string('showtime');
            $table->integer('price')->unsigned();
            $table->string('auditype');
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
        Schema::dropIfExists('now_playing_cinemas');
    }
}
