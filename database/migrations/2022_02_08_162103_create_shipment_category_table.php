<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipmentCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shi_category', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('物流分類的英文代號');
            $table->string('category')->unique()->comment('物流分類');
            $table->timestamps();
        });

        Schema::table('shi_group', function (Blueprint $table) {
            $table->after('id', function ($tb) {
                $tb->unsignedBigInteger('category_fk')->comment('物流分類,foreign key');
                $tb->foreign('category_fk')->references('id')->on('shi_category');
            });
        });

        Schema::create('shi_status', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('content')->nullable();
            $table->string('style')->nullable();
            $table->string('code',3)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shi_group', function (Blueprint $table) {
            $table->dropForeign('shi_group_category_fk_foreign');
            $table->dropColumn('category_fk');
        });
        Schema::dropIfExists('shi_category');
        Schema::dropIfExists('shi_status');
    }
}
