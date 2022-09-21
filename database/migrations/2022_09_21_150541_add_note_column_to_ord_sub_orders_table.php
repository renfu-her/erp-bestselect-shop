<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoteColumnToOrdSubOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ord_sub_orders', function (Blueprint $table) {
            $table->after('statu_code', function ($tb) {
                $tb->string('note')->nullable()->comment('銷貨備註');
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
        Schema::table('ord_sub_orders', function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }
}
