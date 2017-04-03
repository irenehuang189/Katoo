<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropUnusedTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('cinema');
        Schema::dropIfExists('cinema_ticket_class');
        Schema::dropIfExists('cinema_schedule');
        Schema::dropIfExists('cinema_movie');
        Schema::dropIfExists('restaurant_reviews');
        Schema::dropIfExists('movie_genre');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('cinema', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('opening_hour');
            $table->integer('closing_hour');
            $table->string('address');
            $table->string('phone', 15);
            $table->string('url');
            $table->timestamps();
        });
        Schema::create('cinema_ticket_class', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cinema_id')->unsigned();
            $table->string('name');
            $table->integer('price');
            $table->tinyInteger('start_day');
            $table->tinyInteger('end_day');
            $table->timestamps();
        });
        Schema::create('cinema_schedule', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cinema_id')->unsigned();
            $table->integer('movie_id')->unsigned();
            $table->dateTime('datetime');
            $table->timestamps();
        });
        Schema::create('cinema_movie', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('type');
            $table->smallInteger('duration');
            $table->text('synopsis');
            $table->tinyInteger('imdb_rating');
            $table->tinyInteger('rotten_tomatoes_rating');
            $table->string('url');
            $table->timestamps();
        });
        Schema::create('restaurant_reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('zomato_id')->unsigned()->unique();
            $table->integer('restaurant_id')->unsigned();
            $table->text('content');
            $table->decimal('rating', 2, 1);
            $table->timestamps();
        });
        Schema::create('movie_genre', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
    }
}
