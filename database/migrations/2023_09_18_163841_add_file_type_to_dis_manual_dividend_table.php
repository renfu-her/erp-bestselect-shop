<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFileTypeToDisManualDividendTable extends Migration
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

            $table->after('user_id', function ($tb) {
                $tb->string('file_type')->default('sn');
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
        Schema::table('dis_manual_dividend', function (Blueprint $table) {
            //
        });
    }
}
