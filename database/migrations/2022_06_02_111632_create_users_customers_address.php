<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersCustomersAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_customers_address', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usr_customers_id_fk')->comment('消費者ID foreign key');
            $table->string('address')->nullable()->comment('詳細地址');
            $table->integer('city_id')->nullable()->comment('城市ID');
            $table->integer('region_id')->nullable()->comment('區域ID');
            $table->string('addr')->nullable()->comment('簡易地址');
            $table->tinyInteger('is_default_addr')->comment('是否是預設地址 是1、不是0');
        });

        Schema::table('usr_customers', function (Blueprint $table) {
            $table->dropColumn('address');
            $table->dropColumn('city_id');
            $table->dropColumn('region_id');
            $table->dropColumn('addr');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
