<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

class AddSourceFieldInOrdReceivedOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ord_received_orders', function (Blueprint $table) {
            $table->after('id', function ($tb) {
                $tb->string('source_type', 100)->default('ord_orders');
                $tb->integer('source_id')->comment('資料表來源id');
            });
        });

        if (Schema::hasColumn('ord_received_orders', 'source_id'))
        {
            DB::statement('UPDATE ord_received_orders SET source_id = order_id');

            Schema::table('ord_received_orders', function (Blueprint $table) {
                $table->dropColumn('order_id');
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
        Schema::table('ord_received_orders', function (Blueprint $table) {
            $table->dropColumn('source_type');

            $table->after('id', function ($tb) {
                $tb->integer('order_id')->comment('訂單id');
            });
        });

        if (Schema::hasColumn('ord_received_orders', 'order_id'))
        {
            DB::statement('UPDATE ord_received_orders SET order_id = source_id');

            Schema::table('ord_received_orders', function (Blueprint $table) {
                $table->dropColumn('source_id');
            });
        }
    }
}
