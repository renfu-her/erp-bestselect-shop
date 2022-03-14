<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNaviNodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('idx_navi_node', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id')->nullable()->comment('父id');
            $table->string('title')->nullable()->comment('title');
            $table->string('sub_title')->nullable()->comment('group_title,etc');
            $table->string('url')->nullable()->comment('網址');
            $table->integer('event_id')->nullable()->comment('群組id');
            $table->string('event')->nullable()->comment('類型');
            $table->integer('sort')->default(500)->comment('排序');
            $table->tinyInteger('has_child')->default(0)->comment('是否有子項');
            $table->tinyInteger('level')->nullable()->comment('階層');
            $table->string('target')->nullable()->default("_self")->comment('網頁開啟方式');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('idx_navi_node');
    }
}
