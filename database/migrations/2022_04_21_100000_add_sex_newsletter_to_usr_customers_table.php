<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSexNewsletterToUsrCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usr_customers', function (Blueprint $table) {
            $table->after('birthday', function ($tb) {
                $tb->tinyInteger('sex')->nullable()->comment('性別(預設不選) 0:女 1:男');
                $tb->tinyInteger('newsletter')->default(1)->comment('訂閱電子報(預設訂閱) 0:不訂閱 1:訂閱');
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
        Schema::table('usr_customers', function (Blueprint $table) {
            $table->dropColumn('sex');
            $table->dropColumn('newsletter');
        });
    }
}
