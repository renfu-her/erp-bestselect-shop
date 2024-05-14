<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDateFieldToDisManualDividendTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dis_manual_dividend', function (Blueprint $table) {
            //
            $table->after('note', function ($tb) {
                $tb->date('sdate')->comment('起');
                $tb->date('edate')->nullable()->comment('迄');
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
    }
}
