<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGrossProfitColumnToDlvBackAndDlvOutStockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dlv_back', function (Blueprint $table) {
            $table->after('dividend', function ($tb) {
                $tb->decimal('gross_profit', 8, 2)->default(0)->comment('毛利');
            });
        });
        Schema::table('dlv_out_stock', function (Blueprint $table) {
            $table->after('dividend', function ($tb) {
                $tb->decimal('gross_profit', 8, 2)->default(0)->comment('毛利');
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
            'gross_profit',
        ])) {
            Schema::table('dlv_back', function (Blueprint $table) {
                $table->dropColumn('gross_profit');
            });
        }
        if (Schema::hasColumns('dlv_out_stock', [
            'gross_profit',
        ])) {
            Schema::table('dlv_out_stock', function (Blueprint $table) {
                $table->dropColumn('gross_profit');
            });
        }
    }
}
