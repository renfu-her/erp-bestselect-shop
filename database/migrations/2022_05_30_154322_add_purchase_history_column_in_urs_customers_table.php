<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPurchaseHistoryColumnInUrsCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usr_customers', function (Blueprint $table) {
            $table->after('remember_token', function ($tb) {
                $tb->unsignedInteger('order_counts')->default(0)->comment('下單次數');
                $tb->unsignedInteger('total_spending')->default(0)->comment('消費總額');
                $tb->dateTime('latest_order')->nullable()->comment('最新訂單日期');
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
        Schema::table('usr_customers', function (Blueprint $table) {
            //
        });
    }
}
