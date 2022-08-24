<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdjPcsPayingOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pcs_paying_orders', function (Blueprint $table) {
            $table->dropColumn('expecte_pay_date');

            $table->after('balance_date', function ($tb) {
                $tb->dateTime('payment_date')->nullable()->comment('付款日期');
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
            $table->dropColumn('payment_date');

            $table->after('product_grade_id', function ($tb) {
                $tb->dateTime('expecte_pay_date')->nullable()->comment('期望付款日期');
            });
        });
    }
}
