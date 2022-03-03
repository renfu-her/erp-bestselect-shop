<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_users', function (Blueprint $table) {
            $table->id();
            $table->string('name',40);
            $table->string('account',100)->unique();
            $table->string('email',100)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('company_code',10)->comment('公司碼');
            $table->unsignedBigInteger('customer_id')->nullable()->comment('管理者自己的消費者 customer_id');
            $table->string('api_token')->nullable()->default(null);
            $table->uuid('uuid');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['account', 'company_code']);
        });

       



    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usr_users');
    }
}
