<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIdxBannerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('idx_banner', function (Blueprint $table) {
            $table->id()->comment('橫幅廣告ID');
            $table->string('title')->comment('主標題');
            $table->string('event_type')->comment('事件');
            $table->integer('event_id')->nullable()->comment('事件id');
            $table->string('event_url')->nullable()->comment('事件url');
            $table->string('img_pc')->nullable()->comment('圖片_電腦');
            $table->string('img_phone')->nullable()->comment('圖片_手機');
            $table->tinyInteger('is_public')->default(0)->comment('開關 0:false 1:true');
            $table->integer('sort')->default(100)->comment('排序');
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
        Schema::dropIfExists('idx_banner');
    }
}
