<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderProfitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ord_order_profit', function (Blueprint $table) {
            $table->id();   
            $table->integer('order_id')->comment('訂單id');
            $table->integer('order_sn')->comment('訂單sn');
            $table->integer('sub_order_id')->comment('子訂單id');
            $table->integer('sub_order_sn')->comment('子訂單sn');
            $table->integer('style_id')->comment('款式id');
            $table->integer('bonus')->comment('獎金');
            $table->integer('total_bonus')->comment('總獎金');
            $table->integer('customer_id')->comment('消費者id');
            $table->integer('parent_id')->nullable()->comment('父order_id');
            $table->tinyInteger('active')->default('0')->comment('是否成立');
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
        Schema::dropIfExists('ord_order_profit');
    }
}
