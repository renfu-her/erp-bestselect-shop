<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrUsersProjLogisticTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_user_proj_logistics', function (Blueprint $table) {
            $table->id()->comment('物流專案帳號');
            $table->unsignedBigInteger('user_fk')->comment('員工ID,foreign key');
            $table->string('type', 30)->comment('類型 admin user deliveryman');
            $table->string('account')->comment('帳號');
            $table->string('name')->comment('姓名');
            $table->string('password')->nullable()->default(null)->comment('密碼');
            $table->string('api_token')->nullable()->default(null)->comment('物流專案對應類型 api_token');
            $table->tinyInteger('is_open')->default(0)->comment('開關 0:關閉 1:開啟');

            $table->foreign('user_fk')->references('id')->on('usr_users');
            $table->unique(['user_fk', 'type']);
        });

        Schema::create('dlv_logistic_proj_logistic_log', function (Blueprint $table) {
            $table->id()->comment('物流串接物流專案ID');
            $table->unsignedBigInteger('logistic_fk')->comment('物流ID,foreign key');
            $table->string('order_sn', 20)->nullable()->default(null)->comment('託運單sn');
            $table->string('status', 20)->comment('狀態');
            $table->string('text_request')->nullable()->default(null)->comment('上行文本');
            $table->string('text_response')->nullable()->default(null)->comment('下行文本');
            $table->unsignedBigInteger('create_user_fk')->nullable()->comment('建單者ID');
            $table->string('create_user_name', 20)->nullable()->comment('建單者名稱');
            $table->timestamp('created_at');
            $table->foreign('logistic_fk')->references('id')->on('dlv_logistic');
            $table->foreign('create_user_fk')->references('id')->on('usr_users');
        });

        Schema::table('dlv_logistic', function (Blueprint $table) {
            $table->after('memo', function ($tb) {
                $tb->string('projlgt_order_sn', 20)->nullable()->default(null)->comment('託運單sn');
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usr_user_proj_logistics');
        Schema::dropIfExists('dlv_logistic_proj_logistic_log');
    }
}
