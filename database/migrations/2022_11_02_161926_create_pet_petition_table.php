<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePetPetitionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pet_petition', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment('員工id');
            $table->string('title')->comment('主旨');
            $table->text('content')->comment('內容');
            $table->tinyInteger('completed')->default(0)->comment('是否完成');
            $table->timestamps();
        });

        Schema::create('pet_petition_order', function (Blueprint $table) {
            $table->id();
            $table->integer('petition_id')->comment('申議書id');
            $table->integer('source_id')->comment('id');
            $table->string('source_sn')->comment('sn主旨');
            $table->string('source_type')->comment('類型');
        });

        Schema::create('pet_audit', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('step')->comment('流程');
            $table->string('source_type')->comment('類型');
            $table->integer('source_id')->comment('id');
            $table->integer('user_id')->comment('使用者id');
            $table->dateTime('checked_at');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pet_petition');
        Schema::dropIfExists('pet_petition_order');
        Schema::dropIfExists('pet_audit');
    }
}
