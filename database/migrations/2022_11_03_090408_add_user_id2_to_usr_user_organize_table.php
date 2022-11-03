<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserId2ToUsrUserOrganizeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usr_user_organize', function (Blueprint $table) {
            $table->after('user_id', function ($tb) {
                $tb->integer('user_id2')->nullable()->comment('副主管id');
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
