<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSkuColumnToPcsPurchaseInboundTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pcs_purchase_inbound', function (Blueprint $table) {
            $table->after('title', function ($tb) {
                $tb->string('sku', 20)->nullable()->comment('sku碼');
            });
        });
        DB::statement('UPDATE pcs_purchase_inbound SET sku = (SELECT sku FROM prd_product_styles WHERE pcs_purchase_inbound.product_style_id=prd_product_styles.id)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumns('pcs_purchase_inbound', ['sku',])) {
            Schema::table('pcs_purchase_inbound', function (Blueprint $table) {
                $table->dropColumn('sku');
            });
        }
    }
}
