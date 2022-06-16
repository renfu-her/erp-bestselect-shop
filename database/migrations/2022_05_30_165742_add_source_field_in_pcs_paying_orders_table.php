<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

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
                $tb->integer('source_id')->comment('資料表來源id');
                $tb->integer('source_sub_id')->nullable()->comment('來源為訂單時的 sub_orders id');
            });
        });

        if (Schema::hasColumn('pcs_paying_orders', 'source_id'))
        {
            DB::statement('UPDATE pcs_paying_orders SET source_id = purchase_id');

            Schema::table('pcs_paying_orders', function (Blueprint $table) {
                $table->dropColumn('purchase_id');
            });
        }
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

            $table->after('id', function ($tb) {
                $tb->integer('purchase_id')->comment('資料表來源id');
            });
        });

        if (Schema::hasColumn('pcs_paying_orders', 'purchase_id'))
        {
            DB::statement('UPDATE pcs_paying_orders SET purchase_id = source_id');

            Schema::table('pcs_paying_orders', function (Blueprint $table) {
                $table->dropColumn('source_id');
            });
        }
    }
}
