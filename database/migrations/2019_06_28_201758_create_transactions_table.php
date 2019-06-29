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
            $table->date('transaction_date')->comment('거래일시');
            $table->string('handling')->comment('취급점');
            $table->integer('balance')->comment('잔액');
            $table->integer('transaction_amount')->comment('거래금액');
            $table->integer('deposit_amount')->comment('입금금액');
            $table->string('account_book_value')->comment('통장적요');
            $table->integer('payment_amount')->comment('지급금액');
            $table->integer('transaction_principal')->comment('거래원금');
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
