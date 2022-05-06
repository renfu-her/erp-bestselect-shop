<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHostnameInternalFieldInPaymentCreditCardLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ord_payment_credit_card_log', function (Blueprint $table) {
            $table->after('authresurl', function ($tb) {
                $tb->string('hostname_external')->nullable()->comment('外部');
                $tb->string('hostname_internal')->nullable()->comment('內部');
            });

            $table->dropColumn('hostname');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
