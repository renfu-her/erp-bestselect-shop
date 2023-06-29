<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRptOrderDailyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rpt_order_daily', function (Blueprint $table) {
            $table->date('month')->comment('月份');
            $table->integer('price_0')->default(0)->comment('金額');
            $table->integer('qty_0')->default(0)->comment('數量');
            $table->integer('gross_profit_0')->default(0)->comment('毛利');      
            $table->integer('price_1')->default(0)->comment('金額');
            $table->integer('qty_1')->default(0)->comment('數量');
            $table->integer('gross_profit_1')->default(0)->comment('毛利');     
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rpt_order_daily');
    }
}
