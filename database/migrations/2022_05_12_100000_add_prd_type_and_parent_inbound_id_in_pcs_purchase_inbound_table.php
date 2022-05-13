<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrdTypeAndParentInboundIdInPcsPurchaseInboundTable extends Migration
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
                $tb->string('prd_type', 2)->default('p')->comment('商品類別p=商品,c=組合包,ce=組合包元素');
                $tb->unsignedBigInteger('parent_inbound_id')->nullable()->comment('父層入庫來源ID');
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
        Schema::table('pcs_purchase_inbound', function (Blueprint $table) {
            $table->dropColumn('prd_type');
            $table->dropColumn('parent_inbound_id');
        });
    }
}
