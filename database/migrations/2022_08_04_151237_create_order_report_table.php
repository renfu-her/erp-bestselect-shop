<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ord_order_report_daily', function (Blueprint $table) {
            $table->id();
            $table->date('date')->comment('日期');
            $table->integer('price')->default(0)->comment('總金額');
            $table->integer('qty')->default(0)->comment('總件數');
            $table->timestamps();
        });

        Schema::create('ord_order_report_month', function (Blueprint $table) {
            $table->id();
            $table->date('date')->comment('日期');
            $table->integer('price')->default(0)->comment('總金額');
            $table->integer('qty')->default(0)->comment('總件數');
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
        Schema::dropIfExists('ord_order_report_daily');
        Schema::dropIfExists('ord_order_report_month');

    }
}
