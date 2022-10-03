<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransferAtToOrdMonthProfitReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ord_month_profit_report', function (Blueprint $table) {
            $table->after('report_at', function ($tb) {
                $tb->date('transfer_at')->nullable()->comment('匯款日期');
            }); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ord_month_profit_report', function (Blueprint $table) {
            //
        });
    }
}
