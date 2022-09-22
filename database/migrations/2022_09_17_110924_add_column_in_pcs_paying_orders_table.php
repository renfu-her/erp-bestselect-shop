<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnInPcsPayingOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pcs_paying_orders', function (Blueprint $table) {
            $table->after('payee_address', function ($tb) {
                $tb->integer('append_po_id')->nullable()->comment('已付款_付款單id');
                $tb->string('append_po_sn')->nullable()->comment('已付款_付款(消帳)單號');
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
            $table->dropColumn('append_po_id');
            $table->dropColumn('append_po_sn');
        });
    }
}
