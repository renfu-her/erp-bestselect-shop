<?php

use App\Enums\Customer\AccountStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_customers', function (Blueprint $table) {
            $table->id()->comment('消費者ID');
            $table->string('email',100)->unique()->comment('email');
            $table->timestamp('email_verified_at')->nullable()->comment('email驗證');
            $table->string('name',100)->nullable()->comment('姓名');

            $table->string('phone',20)->nullable()->comment('手機');
            $table->timestamp('birthday')->nullable()->comment('生日');

            $table->tinyInteger('acount_status')->default(AccountStatus::close()->value)->comment('帳號狀態 0:未開通 1:開通');
            $table->unsignedBigInteger('bind_customer_id')->nullable()->comment('綁定對象customer_id');
            $table->string('password')->nullable()->comment('密碼');
            $table->string('api_token')->nullable()->default(null)->comment('');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usr_customers');
    }
}
