<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollectionPrdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collection_prd', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('collection_id_fk')->comment('商品群組foreign key');
            $table->foreign('collection_id_fk')->references('id')->on('collection');

            $table->unsignedBigInteger('product_id_fk')->comment('商品ID foreign key');
            $table->foreign('product_id_fk')->references('id')->on('prd_products');

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
        Schema::dropIfExists('collection_prd');
    }
}
