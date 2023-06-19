<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRptProductReportMonthlyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      
        Schema::create('rpt_product_report_monthly', function (Blueprint $table) {
            $table->date('month')->comment('月份');
            $table->integer('product_id')->comment('產品');
            $table->integer('product_style_id')->default(0)->comment('款式');
            $table->integer('price')->default(0)->comment('金額');
            $table->integer('qty')->default(0)->comment('數量');
            $table->integer('gross_profit')->default(0)->comment('毛利');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rpt_product_report_monthly');
    }
}
