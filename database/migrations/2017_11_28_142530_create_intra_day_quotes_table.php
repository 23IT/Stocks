<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIntraDayQuotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('intra_day_quotes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('symbol');
            $table->integer('unknown_value1');
            $table->string('date_stamp');
            $table->string('time_stamp');
            $table->dateTime('datetime_quote');
            $table->float('open');
            $table->float('high');
            $table->float('low');
            $table->float('close');
            $table->integer('volumes');
            $table->integer('unknown_value2');
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
        Schema::dropIfExists('intra_day_quotes');
    }
}
