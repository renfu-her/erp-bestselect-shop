<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerProfitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('usr_customers', function (Blueprint $table) {
            //
            $table->after('id', function ($tb) {
                $tb->string('sn', 20)->nullable()->comment('會員編號');
                $tb->integer('recommend_id')->nullable()->comment('推薦者');     
            });
        });

        Schema::create('usr_customer_profit', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id');
            $table->string('status');
            $table->string('status_title');
            $table->integer('identity_id');
            $table->integer('parent_cusotmer_id')->nullable()->comment('上一代id');
            $table->integer('parent_profit_rate')->default(0)->comment('上一代分潤趴');
            $table->integer('profit_rate')->default(0)->comment('自己分潤趴');
            $table->tinyInteger('has_child')->default(0)->comment('可否有下一代');
            $table->string('profit_type')->comment('分潤回饋方式');
            $table->integer('bank_id');
            $table->string('bank_account')->commnet('銀行帳號');
            $table->string('identity_sn')->comment('身分證字號');
            $table->string('img1')->default('')->comment('身分證正面');
            $table->string('img2')->default('')->comment('身分證反面');
            $table->string('img3')->default('')->comment('存摺封面');
            $table->timestamps();
        });

        Schema::create('acc_banks', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usr_customer_profit');
        Schema::dropIfExists('acc_banks');

    }
}
