<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEstimatedDepotColumnToPcsPurchaseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pcs_purchase', function (Blueprint $table) {
            $table->after('scheduled_date', function ($tb) {
                $tb->unsignedBigInteger('estimated_depot_id')->nullable()->comment('預計入庫倉庫id');
                $tb->string('estimated_depot_name', 20)->nullable()->comment('預計入庫倉庫名稱');
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
        Schema::table('pcs_purchase', function (Blueprint $table) {
            $table->dropColumn('estimated_depot_id');
            $table->dropColumn('estimated_depot_name');
        });
    }
}
