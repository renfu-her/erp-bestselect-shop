<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrCustomerIdentityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_customer_identity', function (Blueprint $table) {
            $table->id()->comment('身分類別ID');
            $table->unsignedBigInteger('customer_id')->comment('消費者ID');
            $table->integer('identity_id')->comment('身份別 員工/企業/同業/團購/消費者');
            $table->string('sn')->nullable()->comment('身份類別編號 例如該企業員工編號');
            $table->integer('level')->nullable()->comment('會員等級 1~N,先留欄位');
            $table->integer('can_bind')->default(0)->comment('可否被綁 0:否 1:是');
            $table->timestamps();
        });

        Schema::create('usr_identity', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('身份別 員工/企業/同業/團購/消費者');
            $table->string('code')->nullable()->comment('識別碼');
            $table->unique(['code']);
        });

        Schema::create('usr_identity_salechannel', function (Blueprint $table) {
            $table->integer('identity_id')->comment('身份ID');
            $table->integer('sale_channel_id')->comment('通路ID');

            $table->unique(['identity_id', 'sale_channel_id']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usr_customer_identity');
        Schema::dropIfExists('usr_identity');
        Schema::dropIfExists('usr_identity_salechannel');     
    }
}
