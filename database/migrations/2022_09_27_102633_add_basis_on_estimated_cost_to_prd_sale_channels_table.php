<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBasisOnEstimatedCostToPrdSaleChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prd_sale_channels', function (Blueprint $table) {
            $table->after('event_edate', function ($tb) {
                $tb->tinyInteger('basis_on_estimated_cost')->nullable()->comment('以成本價為基準');
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
        Schema::table('prd_sale_channels', function (Blueprint $table) {
            //
        });
    }
}
