<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupBuyCompanyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_groupbuy_company', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment("抬頭");
            $table->string('code')->comment("code")->nullable();
            $table->integer('parent_id')->default(0)->comment("父id");
            $table->tinyInteger('is_active')->default(1)->comment("啟用");

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
        Schema::dropIfExists('usr_groupbuy_company');
    }
}
