<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSalechannelIdToRptOrderDailyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rpt_order_daily', function (Blueprint $table) {
            //
            $table->after('month', function ($tb) {
                $tb->integer('salechannel_id')->nullable()->comment('通路id');
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
        Schema::table('rpt_order_daily', function (Blueprint $table) {
            //
        });
    }
}
