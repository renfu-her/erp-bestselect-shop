<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRptProductSaleDailyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rpt_product_sale_daily', function (Blueprint $table) {
            $table->id();
            $table->date('date')->comment('日期');
            $table->integer('sales_type')->comment('銷售類別');
            $table->integer('product_id')->comment('產品id');
            $table->integer('style_id')->comment('款式id');
            $table->integer('qty')->comment('數量');
            $table->integer('price')->comment('金額');
            $table->integer('estimated_cost')->comment('成本');
            $table->integer('gross_profit')->comment('毛利');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rpt_product_sale_daily');
    }
}
