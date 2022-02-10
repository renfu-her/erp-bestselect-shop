<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ord_cart', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id')->comment('商品id');
            $table->integer('product_style_id')->comment('款式id');
            $table->integer('customer_id')->comment('消費這id');
            $table->integer('qty')->comment('數量');
            $table->string('shipment_type')->comment('物流方式');
            $table->integer('shipment_event_id')->nullable()->comment('物流子項id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ord_cart');
    }
}
