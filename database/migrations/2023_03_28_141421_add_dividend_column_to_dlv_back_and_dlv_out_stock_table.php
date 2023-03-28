<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDividendColumnToDlvBackAndDlvOutStockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dlv_back', function (Blueprint $table) {
            $table->after('bonus', function ($tb) {
                $tb->integer('dividend')->default(0)->comment('鴻利');
            });
        });
        Schema::table('dlv_out_stock', function (Blueprint $table) {
            $table->after('bonus', function ($tb) {
                $tb->integer('dividend')->default(0)->comment('鴻利');
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
        if (Schema::hasColumns('dlv_back', [
            'dividend',
        ])) {
            Schema::table('dlv_back', function (Blueprint $table) {
                $table->dropColumn('dividend');
            });
        }
        if (Schema::hasColumns('dlv_out_stock', [
            'dividend',
        ])) {
            Schema::table('dlv_out_stock', function (Blueprint $table) {
                $table->dropColumn('dividend');
            });
        }
    }
}
