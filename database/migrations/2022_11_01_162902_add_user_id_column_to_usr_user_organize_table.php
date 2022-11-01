<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdColumnToUsrUserOrganizeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usr_user_organize', function (Blueprint $table) {
            $table->after('title', function ($tb) {
                $tb->integer('user_id')->nullable()->comment('主管id');
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
        Schema::table('usr_user_organize', function (Blueprint $table) {
            //
        });
    }
}
