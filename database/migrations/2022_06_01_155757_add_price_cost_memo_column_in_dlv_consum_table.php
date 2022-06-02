<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriceCostMemoColumnInDlvConsumTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dlv_consum', function (Blueprint $table) {
            $table->after('product_title', function ($tb) {
                $tb->decimal('unit_price')->default(0)->comment('單價');
                $tb->decimal('unit_cost')->default(0)->comment('單價成本');
                $tb->string('memo')->nullable()->comment('備註');
            });
        });
        Schema::table('pcs_purchase_inbound', function (Blueprint $table) {
            $table->after('title', function ($tb) {
                $tb->decimal('unit_cost')->default(0)->comment('單價成本');
            });
        });
        Schema::table('dlv_receive_depot', function (Blueprint $table) {
            $table->after('product_title', function ($tb) {
                $tb->decimal('unit_cost')->default(0)->comment('單價成本');
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
        Schema::table('dlv_consum', function (Blueprint $table) {
            $table->dropColumn('unit_price');
            $table->dropColumn('cost');
            $table->dropColumn('memo');
        });
        Schema::table('pcs_purchase_inbound', function (Blueprint $table) {
            $table->dropColumn('unit_price');
        });
        Schema::table('dlv_receive_depot', function (Blueprint $table) {
            $table->dropColumn('unit_price');
        });
    }
}
