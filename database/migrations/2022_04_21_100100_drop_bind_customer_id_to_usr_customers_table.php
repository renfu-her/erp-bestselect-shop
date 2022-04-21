<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropBindCustomerIdToUsrCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usr_customers', function (Blueprint $table) {
            $table->dropColumn('bind_customer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('usr_customers', function (Blueprint $table) {
            $table->after('acount_status', function ($tb) {
                $tb->unsignedBigInteger('bind_customer_id')->nullable()->comment('綁定對象customer_id');
            });
        });
    }
}
