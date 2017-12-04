<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClosingQuotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('closing_quotes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('symbol');
            $table->string('date_stamp');
            $table->date('date_quote');
            $table->float('open');
            $table->float('high');
            $table->float('low');
            $table->float('close');
            $table->integer('volumes');
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
        Schema::dropIfExists('closing_quotes');
    }
}
