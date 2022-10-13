<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRptProductSaleDailyCombineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rpt_product_sale_daily_combine', function (Blueprint $table) {
            
            $table->date('date')->comment('日期');
            $table->integer('product_id')->comment('產品id');
            $table->integer('style_id')->comment('款式id');
            $table->integer('on_qty')->comment('數量');
            $table->integer('on_price')->comment('金額');
            $table->integer('on_estimated_cost')->comment('成本');
            $table->integer('on_gross_profit')->comment('毛利');
            $table->integer('off_qty')->comment('數量');
            $table->integer('off_price')->comment('金額');
            $table->integer('off_estimated_cost')->comment('成本');
            $table->integer('off_gross_profit')->comment('毛利');
            $table->integer('total_price')->comment('金額');
            $table->integer('total_gross_profit')->comment('毛利');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rpt_product_sale_daily_combine');
    }
}
