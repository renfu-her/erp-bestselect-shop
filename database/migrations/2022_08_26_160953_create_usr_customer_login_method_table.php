<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrCustomerLoginMethodTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_customer_login_method', function (Blueprint $table) {
            $table->id()->comment('消費者註冊登入方式ID');
            $table->unsignedBigInteger('usr_customer_id_fk')->comment('消費者ID foreign key');
            $table->unsignedSmallInteger('method')->comment('註冊登入方式 1:fb 2:line 參考Enums:Customer:Login');
            $table->string('uid', 255)->comment('令牌');
            $table->timestamps();

            $table->unique(array('usr_customer_id_fk', 'method'));
            $table->foreign('usr_customer_id_fk')->references('id')->on('usr_customers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usr_customer_login_method');
    }
}
