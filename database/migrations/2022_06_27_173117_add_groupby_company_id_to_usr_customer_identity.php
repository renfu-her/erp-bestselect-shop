<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGroupbyCompanyIdToUsrCustomerIdentity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usr_customer_identity', function (Blueprint $table) {
            // Schema::table('ord_orders', function (Blueprint $table) {
            $table->after('can_bind', function ($tb) {
                $tb->integer('groupby_company_id')->nullable()->comment('團購id');
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
        Schema::table('usr_customer_identity', function (Blueprint $table) {
            //
        });
    }
}
