<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActiveDelayDayColumnToOrdOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *x
     * @return void
     */
    public function up()
    {
        Schema::table('ord_orders', function (Blueprint $table) {
            //Ｆ
            $table->after('dividend_lifecycle', function ($tb) {
                $tb->integer('active_delay_day')->nullable()->comment('完成訂單後幾天生效');
                $tb->dateTime('dividend_active_at')->nullable()->comment('紅利優惠生效日');
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
       
    }
}
