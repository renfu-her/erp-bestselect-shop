<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTitleColumnInPcsPurchaseInboundTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pcs_purchase_inbound', function (Blueprint $table) {
            $table->after('product_style_id', function ($tb) {
                $tb->string('title', 40)->comment('商品名稱');
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
            $table->dropColumn('title');
        });
    }
}
