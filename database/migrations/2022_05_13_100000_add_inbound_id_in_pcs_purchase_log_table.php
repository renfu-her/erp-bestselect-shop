<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInboundIdInPcsPurchaseLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pcs_purchase_log', function (Blueprint $table) {
            $table->after('feature', function ($tb) {
                $tb->unsignedBigInteger('inbound_id')->nullable()->comment('入庫ID');
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
        Schema::table('pcs_purchase_log', function (Blueprint $table) {
            $table->dropColumn('inbound_id');
        });
    }
}
