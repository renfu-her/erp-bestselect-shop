<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBalanceDateColumnInOrdReceivedOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ord_received_orders', function (Blueprint $table) {
            $table->after('product_grade_id', function ($tb) {
                $tb->dateTime('balance_date')->nullable();
            });
        });

        Schema::table('pcs_paying_orders', function (Blueprint $table) {
            $table->dropColumn('pay_date');

            $table->after('expecte_pay_date', function ($tb) {
                $tb->dateTime('balance_date')->nullable();
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
        Schema::table('ord_received_orders', function (Blueprint $table) {
            $table->dropColumn('balance_date');
        });

        Schema::table('pcs_paying_orders', function (Blueprint $table) {
            $table->dropColumn('balance_date');

            $table->after('expecte_pay_date', function ($tb) {
                $tb->string('pay_date')->nullable()->comment('付款日期');
            });
        });
    }
}
