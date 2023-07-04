<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAppFieldColumnToOpgOnePageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('opg_one_page', function (Blueprint $table) {
            //
            $table->after('active', function ($tb) {
                $tb->integer('app')->default(0)->comment('app顯示');
                $tb->string('img')->nullable()->comment('圖片');
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
        Schema::table('opg_one_page', function (Blueprint $table) {
            //
        });
    }
}
