<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrCustomerReportDailyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       

        Schema::create('usr_customer_report_daily', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id')->comment('客戶id');
            $table->date('date')->comment('日期');
            $table->integer('price')->default(0)->comment('總金額');
            $table->timestamps();
        });

        Schema::create('usr_customer_report_month', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id')->comment('客戶id');
            $table->date('date')->comment('日期');
            $table->integer('price')->default(0)->comment('總金額');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usr_customer_report_daily');
        Schema::dropIfExists('usr_customer_report_month');
    }
}
