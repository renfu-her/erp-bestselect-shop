<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRptUserPerformanceDailyReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rpt_user_performance_daily_report', function (Blueprint $table) {
            $table->date('date')->comment('日期');
            $table->integer('user_id')->comment('員工id');
            $table->integer('price')->comment('金額');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rpt_user_performance_daily_report');
    }
}
