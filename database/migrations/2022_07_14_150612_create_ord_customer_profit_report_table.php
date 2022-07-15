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
        Schema::create('ord_customer_profit_report', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id')->comment('分潤者id');
            $table->integer('bonus')->comment('獎金');
            $table->integer('qty')->comment('數量');
            $table->date('report_at')->comment('報表月');
            $table->datetime('checked_at')->nullable()->comment('確認日');
            $table->timestamps();
        });

        /*
        Schema::create('ord_customer_profit_report_profit', function (Blueprint $table) {
            $table->id();
            $table->integer('report_id')->comment('報表id');
            $table->integer('profit_id')->comment('分潤id');
        });
        */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ord_customer_profit_report');
       // Schema::dropIfExists('ord_customer_profit_report_profit');

    }
}
