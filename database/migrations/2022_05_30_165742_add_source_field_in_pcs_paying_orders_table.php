<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSourceFieldInPcsPayingOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pcs_paying_orders', function (Blueprint $table) {
            $table->after('id', function ($tb) {
                $tb->string('source_type', 100)->default('pcs_purchase');
            });

            $table->renameColumn('purchase_id', 'source_id');

            $table->after('purchase_id', function ($tb) {
                $tb->integer('source_sub_id')->nullable()->comment('來源為訂單時的 sub_orders id');
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
        Schema::table('pcs_paying_orders', function (Blueprint $table) {
            $table->dropColumn('source_type');

            $table->dropColumn('source_sub_id');

            $table->renameColumn('source_id', 'purchase_id');
        });
    }
}
