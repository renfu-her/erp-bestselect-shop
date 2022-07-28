<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLogisticStatusColumnToOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ord_sub_orders', function (Blueprint $table) {
            $table->after('close_date', function ($tb) {
                $tb->string('logistic_status_code', 10)->nullable()->comment('物流狀態ID');
                $tb->string('logistic_status', 20)->nullable()->comment('物流狀態 檢貨中/理貨中/待配送');
            });
        });
        Schema::table('csn_consignment', function (Blueprint $table) {
            $table->after('memo', function ($tb) {
                $tb->string('logistic_status_code', 10)->nullable()->comment('物流狀態ID');
                $tb->string('logistic_status', 20)->nullable()->comment('物流狀態 檢貨中/理貨中/待配送');
            });
        });
        Schema::table('csn_orders', function (Blueprint $table) {
            $table->after('memo', function ($tb) {
                $tb->string('logistic_status_code', 10)->nullable()->comment('物流狀態ID');
                $tb->string('logistic_status', 20)->nullable()->comment('物流狀態 檢貨中/理貨中/待配送');
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
        if (Schema::hasColumns('ord_sub_orders', ['logistic_status',])) {
            Schema::table('ord_sub_orders', function (Blueprint $table) {
                $table->dropColumn('logistic_status_code');
                $table->dropColumn('logistic_status');
            });
        }
        if (Schema::hasColumns('csn_consignment', ['logistic_status',])) {
            Schema::table('csn_consignment', function (Blueprint $table) {
                $table->dropColumn('logistic_status_code');
                $table->dropColumn('logistic_status');
            });
        }
        if (Schema::hasColumns('csn_orders', ['logistic_status',])) {
            Schema::table('csn_orders', function (Blueprint $table) {
                $table->dropColumn('logistic_status_code');
                $table->dropColumn('logistic_status');
            });
        }
    }
}
