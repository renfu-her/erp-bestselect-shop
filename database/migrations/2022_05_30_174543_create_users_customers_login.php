<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersCustomersLogin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_customers_login', function (Blueprint $table) {
            $table->id()->comment('消費者註冊登入方式ID');
            $table->unsignedBigInteger('usr_customers_id_fk')->comment('消費者ID foreign key');
            $table->unsignedSmallInteger('login_method')->comment('註冊登入方式');
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
        Schema::dropIfExists('usr_customers_login');
    }
}
