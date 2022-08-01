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
        Schema::create('usr_organize', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique()->comment('部門名稱');
            $table->string('parent')->comment('父階');
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
        Schema::dropIfExists('usr_organize');
    }
}
