<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtraIdColumnInPcsPurchaseLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pcs_purchase_log', function (Blueprint $table) {
            $table->after('event_id', function ($tb) {
                $tb->unsignedBigInteger('extra_id')->comment('額外的ID');
            });
        });

        Schema::create('dlv_element_back', function (Blueprint $table) {
            $table->id()->comment('退貨元素ID');
            $table->unsignedBigInteger('delivery_id')->comment('出貨單ID');
            $table->unsignedBigInteger('bac_papa_id')->comment('退貨列表ID');
//            $table->unsignedBigInteger('event_item_id')->nullable()->comment('事件物品ID 訂單ord_items.id、寄倉csn_consignment_items.id');
            $table->unsignedBigInteger('rcv_depot_id')->nullable()->comment('dlv_receive_depot_id');
            $table->integer('qty')->comment('退貨數量');
            $table->string('memo')->nullable()->comment('備註');
            $table->timestamps();
        });

        Schema::table('dlv_bac_papa', function (Blueprint $table) {
            $table->after('id', function ($tb) {
                $tb->string('type', 10)->comment('類別 DlvBackPapaType back:退貨 out:缺貨');
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
        if (Schema::hasColumns('pcs_purchase_log', ['extra_id'])) {
            Schema::table('pcs_purchase_log', function (Blueprint $table) {
                $table->dropColumn('extra_id');
            });
        }
        if (Schema::hasColumns('dlv_bac_papa', ['type'])) {
            Schema::table('dlv_bac_papa', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }

        Schema::dropIfExists('dlv_element_back');
    }
}
