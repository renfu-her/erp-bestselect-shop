<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCloseDateColumnInOrdSubOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ord_sub_orders', function (Blueprint $table) {
            $table->after('statu_code', function ($tb) {
                $tb->dateTime('close_date')->nullable()->comment('結案日期');
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
        Schema::table('ord_sub_orders', function (Blueprint $table) {
            $table->dropColumn('close_date');
        });
    }
}
