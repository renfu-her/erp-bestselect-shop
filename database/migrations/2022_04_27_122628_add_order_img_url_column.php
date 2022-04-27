<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderImgUrlColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //

        Schema::table('ord_items', function (Blueprint $table) {
            $table->string('img_url')->default('')->nullable();
        });

        Schema::table('prd_product_images', function (Blueprint $table) {
            $table->integer('sort')->default(50)->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
