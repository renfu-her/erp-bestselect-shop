<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceiveDepotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receive_depot', function (Blueprint $table) {
            $table->id()->comment('收貨倉庫ID');
            $table->unsignedBigInteger('sub_order_id')->comment('出貨單號ID');
            $table->unsignedBigInteger('depot_id')->comment('收貨倉庫ID');
            $table->string('depot_name')->comment('倉庫名稱');
            $table->unsignedBigInteger('product_style_id')->comment('商品款式ID');
            $table->integer('qty')->comment('數量');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('receive_depot');
    }
}
