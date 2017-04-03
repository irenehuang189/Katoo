<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNowPlayingInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('now_playing_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('poster');
            $table->string('genre');
            $table->string('duration');
            $table->string('trailer');
            $table->text('plot');
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
        Schema::dropIfExists('now_playing_infos');
    }
}
