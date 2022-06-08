<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReviseCustomerNamePhoneColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumns('usr_customers', [
            'phone',
        ])) {
            Schema::table('usr_customers', function (Blueprint $table) {
                $table->dropColumn('phone');
            });
        }

        Schema::table('usr_customers_address', function (Blueprint $table) {
            $table->after('usr_customers_id_fk', function ($tb) {
                $tb->string('name', 100)->comment('收件人姓名');
                $tb->string('phone')->comment('收件電話');
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
        Schema::table('usr_customers', function (Blueprint $table) {
            //
        });
    }
}
