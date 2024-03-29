<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModifiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modifies', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_id');
            $table->integer('transaction_id');
            $table->integer('user_id');
            $table->string('subject');
            $table->string('prev_subject');
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
        Schema::dropIfExists('modifies');
    }
}
