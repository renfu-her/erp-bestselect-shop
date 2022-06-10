<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalechannelStyleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prd_salechannel_style_stock', function (Blueprint $table) {
            $table->integer('style_id')->comment('款式id');
            $table->integer('sale_channel_id')->comment('通路商id');
            $table->integer('in_stock')->default(0)->comment('庫存');

            $table->unique(['style_id', 'sale_channel_id']);
        });

        Schema::create('prd_salechannel_style_price', function (Blueprint $table) {
            $table->integer('style_id')->comment('款式id');
            $table->integer('sale_channel_id')->comment('通路商id');
            $table->integer('dealer_price')->default(0)->comment('經銷價');
            $table->integer('origin_price')->default(0)->comment('定價');
            $table->integer('price')->default(0)->comment('售價');         
            $table->integer('bonus')->default(0)->comment('獎金');
            $table->integer('dividend')->default(0)->comment('鴻利');

            $table->unique(['style_id', 'sale_channel_id']);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prd_salechannel_style_price');
        Schema::dropIfExists('prd_salechannel_style_stock');
    }
}
