<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountReceivedManagerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_received_account', function (Blueprint $table) {
            $table->id();
            $table->string('status_code', 10)->default(0)->comment('應收帳款狀態');// 0:未入款 1:已入款
            $table->integer('append_received_order_id')->nullable()->comment('已入款_收款單id');
            $table->string('sn')->nullable()->comment('已入款_收款(消帳)單號');
            $table->decimal('amt_net', 12, 2)->default(0)->comment('已入款_收款金額');
            $table->dateTime('posting_date')->nullable()->comment('入款日期');
            $table->integer('drawee_id')->nullable()->comment('對象id');// usr_customers or depot id
            $table->string('drawee_name')->nullable()->comment('對象名稱');// usr_customers or depot name
            $table->string('drawee_phone')->nullable()->comment('對象名稱');// usr_customers or depot name
            $table->string('drawee_address')->nullable()->comment('對象名稱');// usr_customers or depot name
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
        Schema::dropIfExists('acc_received_account');
    }
}
