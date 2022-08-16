<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIdxNewsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('idx_news', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('標題');
            $table->text('content')->comment('內容');
            $table->string('weight')->comment('權重');
            $table->string('weight_title')->comment('權重標題');
            $table->dateTime('expire_time')->comment('過期時間');
            $table->string('type')->nullable()->comment('公告對象');
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
        Schema::dropIfExists('idx_news');
    }
}
