<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductPickup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prd_pickup', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('product_id_fk')->comment('商品, foreign key');
            $table->foreign('product_id_fk')->references('id')->on('prd_products');

            $table->unsignedBigInteger('depot_id_fk')->comment('自取倉庫, foreign key');
            $table->foreign('depot_id_fk')->references('id')->on('depot');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prd_pickup');
    }
}
