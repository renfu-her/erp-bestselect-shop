<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductTitleAndPrdTypeColumnInPcsPurchaseLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pcs_purchase_log', function (Blueprint $table) {
            $table->after('inbound_id', function ($tb) {
                $tb->string('product_title', 40)->nullable()->default(null)->comment('商品名稱');
                $tb->string('prd_type', 2)->nullable()->default(null)->comment('商品類別p=商品,c=組合包,ce=組合包元素');
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
        Schema::table('pcs_purchase_log', function (Blueprint $table) {
            $table->dropColumn('product_title');
            $table->dropColumn('prd_type');
        });
    }
}
