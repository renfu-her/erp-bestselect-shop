<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdDividendTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ord_dividend', function (Blueprint $table) {
            $table->id();
            $table->string('order_sn')->comment('訂單sn');
            $table->integer('customer_dividend_id')->comment('會員紅利id');
            $table->integer('dividend')->comment('紅利');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ord_dividend');
    }
}
