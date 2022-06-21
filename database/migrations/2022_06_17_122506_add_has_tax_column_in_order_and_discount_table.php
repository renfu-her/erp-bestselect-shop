<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHasTaxColumnInOrderAndDiscountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ord_orders', function (Blueprint $table) {
            $table->after('dlv_fee', function ($tb) {
                $tb->tinyInteger('dlv_taxation')->default(1)->comment('應稅與否');
            });
        });

        Schema::table('ord_discounts', function (Blueprint $table) {
            $table->tinyInteger('discount_taxation')->default(1)->comment('應稅與否');
        });

        Schema::table('acc_received', function (Blueprint $table) {
            $table->after('accountant_id_fk', function ($tb) {
                $tb->tinyInteger('taxation')->default(1)->comment('應稅與否');
                $tb->string('summary')->nullable()->comment('摘要');
            });
        });

        Schema::table('acc_payable', function (Blueprint $table) {
            $table->after('accountant_id_fk', function ($tb) {
                $tb->tinyInteger('taxation')->default(1)->comment('應稅與否');
                $tb->string('summary')->nullable()->comment('摘要');
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
        Schema::table('ord_orders', function (Blueprint $table) {
            $table->dropColumn('dlv_taxation');
        });

        Schema::table('ord_discounts', function (Blueprint $table) {
            $table->dropColumn('discount_taxation');
        });

        Schema::table('acc_received', function (Blueprint $table) {
            $table->dropColumn('summary');
        });

        Schema::table('acc_payable', function (Blueprint $table) {
            $table->dropColumn('summary');
        });
    }
}
