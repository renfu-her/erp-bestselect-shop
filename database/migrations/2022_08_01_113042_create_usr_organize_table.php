<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrOrganizeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_user_organize', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('部門名稱');
            $table->integer('parent')->default(0)->comment('父階');
            $table->integer('level')->default(0)->comment('階層');
            $table->integer('lft')->nullable()->comment('lft');
            $table->integer('rgt')->nullable()->comment('rgt');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usr_user_organize');
    }
}
