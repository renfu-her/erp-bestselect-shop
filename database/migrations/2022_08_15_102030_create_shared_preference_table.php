<?php

use App\Enums\Globals\StatusOffOn;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSharedPreferenceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shared_preference', function (Blueprint $table) {
            $table->id()->comment('一般設定ID');
            $table->string('category', 40)->comment('類別');
            $table->string('event', 40)->comment('事件');
            $table->string('feature', 40)->comment('功能');
            $table->string('title')->comment('名稱');
            $table->integer('type')->comment('型態 0:開關 1:失敗成功 2:enum');
            $table->integer('status')->default(StatusOffOn::Off()->value)->comment('狀態 0:關 1:開 ； 其他ID');
            $table->integer('order')->default(10)->comment('排序');
            $table->timestamps();
        });

        Schema::create('shared_preference_enum', function (Blueprint $table) {
            $table->id()->comment('設定enumID');
            $table->string('category', 40)->comment('類別');
            $table->string('event', 40)->comment('事件');
            $table->string('feature', 40)->comment('功能');
            $table->string('code', 10)->comment('代碼');
            $table->integer('title')->default(StatusOffOn::Off()->value)->comment('名稱');
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
        Schema::dropIfExists('shared_preference');
        Schema::dropIfExists('shared_preference_enum');
    }
}
