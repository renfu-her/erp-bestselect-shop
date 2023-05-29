<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFruitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fru_fruits', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('sub_title');
            $table->string('place');
            $table->string('season');
            $table->string('pic');
            $table->string('text');
            $table->string('status');
            $table->string('link');       
        });

        Schema::create('fru_collections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('sub_title');
        });

        Schema::create('fru_collection_fruit', function (Blueprint $table) { 
            $table->integer('collection_id');
            $table->integer('fruit_id');
            $table->integer('sort');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fruits');
    }
}
