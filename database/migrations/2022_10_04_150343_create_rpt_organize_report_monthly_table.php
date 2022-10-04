<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRptOrganizeReportMonthlyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rpt_organize_report_monthly', function (Blueprint $table) {
            $table->id();
            $table->date('month')->comment('月份');
            $table->integer('organize_id')->comment('組織id');
            $table->integer('on_price')->default(0)->comment('線上營業額');
            $table->integer('on_gross_profit')->default(0)->comment('線上毛利');
            $table->integer('off_price')->default(0)->comment('線下營業額');
            $table->integer('off_gross_profit')->default(0)->comment('線下毛利');
            $table->integer('total_price')->default(0)->comment('營業額');
            $table->integer('total_gross_profit')->default(0)->comment('毛利');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rpt_organize_report_monthly');
    }
}
