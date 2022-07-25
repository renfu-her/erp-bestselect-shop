<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdjOrdReceivedOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ord_received_orders', function (Blueprint $table) {
            $table->after('memo', function ($tb) {
                $tb->integer('drawee_id')->nullable()->comment('對象id');
                $tb->string('drawee_name')->nullable()->comment('對象名稱');
                $tb->string('drawee_phone')->nullable()->comment('對象名稱');
                $tb->string('drawee_address')->nullable()->comment('對象名稱');
            });
        });

        Schema::table('acc_received_account', function (Blueprint $table) {
            $table->dropColumn('drawee_id');
            $table->dropColumn('drawee_name');
            $table->dropColumn('drawee_phone');
            $table->dropColumn('drawee_address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
