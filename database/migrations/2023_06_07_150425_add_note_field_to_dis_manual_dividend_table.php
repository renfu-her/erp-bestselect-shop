<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoteFieldToDisManualDividendTable extends Migration
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
            $table->after('category_title', function ($tb) {
                $tb->string('note')->nullable();
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
