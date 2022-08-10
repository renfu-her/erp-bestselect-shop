<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdxNewsColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('idx_news', function (Blueprint $table) {
            $table->after('type', function ($tb) {
                $tb->unsignedBigInteger('usr_users_id_fk')->comment('公告者');
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
        Schema::table('idx_news', function (Blueprint $table) {
            //
        });
    }
}
