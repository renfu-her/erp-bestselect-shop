<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdCustomerProfitReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ord_month_profit_report', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('create_user_id')->comment('創建者id');
            $table->integer('bonus')->comment('總獎金');
            $table->integer('qty')->comment('總數量');
            $table->date('report_at')->comment('報表月');
            $table->timestamps();
        });


        Schema::create('ord_customer_profit_report', function (Blueprint $table) {
            $table->id();
            $table->integer('month_profit_report_id')->comment('月報id');
            $table->integer('customer_id')->comment('分潤者id');
            $table->integer('bonus')->comment('獎金');
            $table->integer('qty')->comment('數量');
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
        Schema::dropIfExists('ord_month_profit_report');
        Schema::dropIfExists('ord_customer_profit_report');

    }
}
