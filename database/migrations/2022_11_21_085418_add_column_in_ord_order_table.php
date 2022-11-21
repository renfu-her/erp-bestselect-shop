<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnInOrdOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ord_orders', function (Blueprint $table) {
            $table->after('buyer_ubn', function ($tb) {
                $tb->string('buyer_email', 100)->nullable()->comment('買受人電子信箱');
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
            $table->dropColumn('buyer_email');
        });
    }
}
