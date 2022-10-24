<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDepartmentPerformanceReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //

        Schema::create('rpt_department_performance_daily_report', function (Blueprint $table) {
            $table->date('date')->comment('日期');
            $table->integer('organize_id')->comment('組織');
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
        Schema::dropIfExists('rpt_department_performance_daily_report');
    }
}
