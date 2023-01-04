<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCountryColumnToOpgOnePageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('opg_one_page', function (Blueprint $table) {
            $table->after('title', function ($tb) {
                $tb->string('country')->nullable()->comment('國家名稱');
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
