<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddErpColumnToUsrUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usr_users', function (Blueprint $table) {
            $table->after('email', function ($tb) {
                $tb->string('title')->nullable()->comment('職稱');
                $tb->string('company')->nullable()->comment('公司');
                $tb->string('department')->nullable()->comment('部門');
                $tb->string('group')->nullable()->comment('組');
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
