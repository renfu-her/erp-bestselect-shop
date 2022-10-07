<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnInAccStituteOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('acc_stitute_order_items', function (Blueprint $table) {
            $table->after('memo', function ($tb) {
                $tb->string('po_note')->nullable()->comment('備註');
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
        if (Schema::hasColumns('acc_stitute_order_items', ['po_note'])) {
            Schema::table('acc_stitute_order_items', function (Blueprint $table) {
                $table->dropColumn('po_note');
            });
        }
    }
}
