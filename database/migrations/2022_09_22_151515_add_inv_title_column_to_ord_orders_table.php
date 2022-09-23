<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvTitleColumnToOrdOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ord_orders', function (Blueprint $table) {
            $table->after('category', function ($tb) {
                $tb->string('inv_title', 100)->nullable()->comment('發票抬頭');
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
        if (Schema::hasColumns('ord_orders', [
            'inv_title',
        ])) {
            Schema::table('ord_orders', function (Blueprint $table) {
                $table->dropColumn('inv_title');
            });
        }
    }
}
