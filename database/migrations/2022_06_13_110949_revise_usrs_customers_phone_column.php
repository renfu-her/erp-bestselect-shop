<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReviseUsrsCustomersPhoneColumn extends Migration
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
            'email_verified_at',
        ])) {
            Schema::table('usr_customers', function (Blueprint $table) {
                $table->after('email_verified_at', function ($tb) {
                    $tb->string('phone', 30)->nullable()->comment('會員電話')->change();
                });
            });
        }
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
