<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHlpAddrTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loc_addr', function (Blueprint $table) {
            $table->id();
            $table->string('title', 20);
            $table->string('zipcode', 5)->nullable();
            $table->integer('parent_id')->nullable();
            $table->integer('service_area_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loc_addr');
    }
}
