<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUnitCostColumnInOrdItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ord_items', function (Blueprint $table) {
            $table->after('price', function ($tb) {
                $tb->decimal('unit_cost')->nullable()->default(null)->comment('單價成本');
            });
        });
        Schema::table('csn_consignment_items', function (Blueprint $table) {
            $table->after('price', function ($tb) {
                $tb->decimal('unit_cost')->nullable()->default(null)->comment('單價成本');
            });
        });
        Schema::table('csn_order_items', function (Blueprint $table) {
            $table->after('price', function ($tb) {
                $tb->decimal('unit_cost')->nullable()->default(null)->comment('單價成本');
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
        if (Schema::hasColumns('ord_items', [
            'unit_cost',
        ])) {
            Schema::table('ord_items', function (Blueprint $table) {
                $table->dropColumn('unit_cost');
            });
        }
        if (Schema::hasColumns('csn_consignment_items', [
            'unit_cost',
        ])) {
            Schema::table('csn_consignment_items', function (Blueprint $table) {
                $table->dropColumn('unit_cost');
            });
        }
        if (Schema::hasColumns('csn_order_items', [
            'unit_cost',
        ])) {
            Schema::table('csn_order_items', function (Blueprint $table) {
                $table->dropColumn('unit_cost');
            });
        }
    }
}
