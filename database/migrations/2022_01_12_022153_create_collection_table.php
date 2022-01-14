<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collection', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('商品群組名稱');
            $table->string('url')->unique()->comment('商品群組Url連結');
//            $table->string('image_url')->comment('商品群組圖片Url連結');
            $table->string('meta_title')->nullable()->comment('商品群組網頁標題');
            $table->string('meta_description')->nullable()->comment('商品群組網頁描述');
            $table->boolean('is_public')->default(false)->comment('商品群組狀態：公開、隱藏');
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
        Schema::dropIfExists('collection');
    }
}
