<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddViewModeColumnToOpgOnePageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
        Schema::table('opg_one_page', function (Blueprint $table) {
            $table->after('online_pay', function ($tb) {
                $tb->tinyInteger('view_mode')->default(1)->comment('檢視模式');
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
