<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCarrierTypeColumnToOrdOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ord_orders', function (Blueprint $table) {
            $table->after('gui_number', function ($tb) {
                $tb->string('category', 10)->nullable()->comment('發票種類');
                $tb->string('buyer_ubn', 100)->nullable()->comment('買受人統一編號');
                $tb->integer('love_code')->nullable()->comment('捐贈碼');
                $tb->string('carrier_type', 2)->nullable()->comment('載具類別');
                $tb->string('carrier_num')->nullable()->comment('載具編號');
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
        if (Schema::hasColumns('ord_orders', [
            'carrier_type',
        ])) {
            Schema::table('ord_orders', function (Blueprint $table) {
                $table->dropColumn('category');
                $table->dropColumn('buyer_ubn');
                $table->dropColumn('carrier_type');
                $table->dropColumn('carrier_num');
                $table->dropColumn('love_code');
            });
        }
    }
}
