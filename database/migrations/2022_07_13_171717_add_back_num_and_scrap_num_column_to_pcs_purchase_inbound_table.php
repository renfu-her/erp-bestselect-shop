<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBackNumAndScrapNumColumnToPcsPurchaseInboundTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pcs_purchase_inbound', function (Blueprint $table) {
            $table->after('consume_num', function ($tb) {
                $tb->integer('back_num')->default(0)->comment('退貨數量');
                $tb->integer('scrap_num')->default(0)->comment('報廢數量');
            });
        });
        Schema::table('dlv_receive_depot', function (Blueprint $table) {
            $table->after('csn_arrived_qty', function ($tb) {
                $tb->integer('back_qty')->default(0)->comment('退貨數量');
            });
        });
        Schema::table('dlv_consum', function (Blueprint $table) {
            $table->after('qty', function ($tb) {
                $tb->integer('back_qty')->default(0)->comment('退貨數量');
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
        if (Schema::hasColumns('pcs_purchase_inbound', [
            'back_num',
        ])) {
            Schema::table('pcs_purchase_inbound', function (Blueprint $table) {
                $table->dropColumn('back_num');
                $table->dropColumn('scrap_num');
            });
        }
        if (Schema::hasColumns('dlv_receive_depot', [
            'back_qty',
        ])) {
            Schema::table('dlv_receive_depot', function (Blueprint $table) {
                $table->dropColumn('back_qty');
            });
        }
        if (Schema::hasColumns('dlv_consum', [
            'back_qty',
        ])) {
            Schema::table('dlv_consum', function (Blueprint $table) {
                $table->dropColumn('back_qty');
            });
        }
    }
}
