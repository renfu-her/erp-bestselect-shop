<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnInPrdSaleChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prd_sale_channels', function (Blueprint $table) {
            $table->after('discount', function ($tb) {
                $tb->tinyInteger('has_bonus')->default('1')->comment('有無獎金？');
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
        if (Schema::hasColumns('prd_sale_channels', ['has_bonus'])) {
            Schema::table('prd_sale_channels', function (Blueprint $table) {
                $table->dropColumn('has_bonus');
            });
        }
    }
}
