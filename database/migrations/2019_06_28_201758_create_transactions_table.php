<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_id');
            $table->string('title')->default(NULL)->comment('(Detail)');
            $table->date('transaction_date')->comment('거래일시 (Date)');
            $table->string('handling')->comment('취급점 (Contest)');
            $table->integer('balance')->comment('잔액 (Balance)');
            $table->integer('transaction_amount')->comment('거래금액 (Amount)');
            $table->integer('deposit_amount')->comment('입금금액 (입금 금액 == 지급 금액이 0원, 거래금액 > 0 이면 Card');
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
        Schema::dropIfExists('transactions');
    }
}
