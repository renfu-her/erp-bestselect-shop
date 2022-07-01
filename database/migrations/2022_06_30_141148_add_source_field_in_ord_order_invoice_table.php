<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

class AddSourceFieldInOrdOrderInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ord_order_invoice', function (Blueprint $table) {
            $table->after('id', function ($tb) {
                $tb->string('source_type', 100)->default('ord_orders');
                $tb->integer('source_id')->comment('資料表來源id');
                $tb->text('merge_source_id')->nullable()->comment('合併來源id');
            });
        });

        if (Schema::hasColumn('ord_order_invoice', 'source_id') && Schema::hasColumn('ord_order_invoice', 'merge_source_id'))
        {
            DB::statement('UPDATE ord_order_invoice SET source_id = order_id');
            DB::statement('UPDATE ord_order_invoice SET merge_source_id = merge_order_id');

            Schema::table('ord_order_invoice', function (Blueprint $table) {
                $table->dropColumn('order_id');
                $table->dropColumn('merge_order_id');
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
        Schema::table('ord_order_invoice', function (Blueprint $table) {
            $table->dropColumn('source_type');

            $table->after('id', function ($tb) {
                $tb->integer('order_id')->comment('訂單id');
                $tb->text('merge_order_id')->nullable()->comment('合併開立訂單id');
            });
        });

        if (Schema::hasColumn('ord_order_invoice', 'order_id') && Schema::hasColumn('ord_order_invoice', 'merge_order_id'))
        {
            DB::statement('UPDATE ord_order_invoice SET order_id = source_id');
            DB::statement('UPDATE ord_order_invoice SET merge_order_id = merge_source_id');

            Schema::table('ord_order_invoice', function (Blueprint $table) {
                $table->dropColumn('source_id');
                $table->dropColumn('merge_source_id');
            });
        }
    }
}
