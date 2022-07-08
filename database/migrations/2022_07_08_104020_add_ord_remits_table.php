<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrdRemitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ord_remits', function (Blueprint $table) {
            $table->id()->comment('訂單匯款');
            $table->unsignedBigInteger('order_id')->nullable()->comment('訂單id');
            $table->string('name', 100)->comment('匯款人姓名');
            $table->decimal('price')->comment('金額');
            $table->dateTime('remit_date')->comment('匯款日期');
            $table->string('bank_code', 5)->comment('末5碼');
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
        Schema::dropIfExists('ord_remits');
    }
}
