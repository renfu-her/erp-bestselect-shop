<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePcsPayingOrdersItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pcs_paying_orders', function (Blueprint $table) {
            $table->id()->comment('代墊單id 採購時使用');
            $table->integer('purchase_id')->comment('採購id');
            $table->tinyInteger('type')->nullable()->comment('代墊單類型 0:訂金 1:尾款');
            $table->string('order_num')->comment('代墊單號');
            $table->string('price')->nullable()->comment('金額');
            $table->string('pay_date')->nullable()->comment('付款日期');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pcs_paying_orders');
    }
}
