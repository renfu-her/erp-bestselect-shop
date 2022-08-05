<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProjlgtOrderSnColumnToOrdSubOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ord_sub_orders', function (Blueprint $table) {
            $table->after('logistic_status', function ($tb) {
                $tb->string('projlgt_order_sn', 20)->nullable()->default(null)->comment('託運單sn');
            });
        });
        Schema::table('csn_consignment', function (Blueprint $table) {
            $table->after('logistic_status', function ($tb) {
                $tb->string('projlgt_order_sn', 20)->nullable()->default(null)->comment('託運單sn');
            });
        });
        Schema::table('csn_orders', function (Blueprint $table) {
            $table->after('logistic_status', function ($tb) {
                $tb->string('projlgt_order_sn', 20)->nullable()->default(null)->comment('託運單sn');
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
        if (Schema::hasColumns('ord_sub_orders', ['projlgt_order_sn',])) {
            Schema::table('ord_sub_orders', function (Blueprint $table) {
                $table->dropColumn('projlgt_order_sn');
            });
        }
        if (Schema::hasColumns('csn_consignment', ['projlgt_order_sn',])) {
            Schema::table('csn_consignment', function (Blueprint $table) {
                $table->dropColumn('projlgt_order_sn');
            });
        }
        if (Schema::hasColumns('csn_orders', ['projlgt_order_sn',])) {
            Schema::table('csn_orders', function (Blueprint $table) {
                $table->dropColumn('projlgt_order_sn');
            });
        }
    }
}
