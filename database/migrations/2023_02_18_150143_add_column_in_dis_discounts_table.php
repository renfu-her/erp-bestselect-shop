<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnInDisDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dis_discounts', function (Blueprint $table) {
            $table->after('end_date', function ($tb) {
                $tb->string('mail_subject')->nullable()->comment('到期通知信件主旨');
                $tb->text('mail_content')->nullable()->comment('到期通知信件內容');
            });
        });

        Schema::table('usr_customer_coupon', function (Blueprint $table) {
            $table->after('used_at', function ($tb) {
                $tb->string('mail_subject')->nullable()->comment('到期通知信件主旨');
                $tb->text('mail_content')->nullable()->comment('到期通知信件內容');
                $tb->dateTime('mail_sended_at')->nullable()->comment('到期通知信寄送時間');
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
        if (Schema::hasColumns('dis_discounts', [
            'mail_subject',
            'mail_content',
        ])) {
            Schema::table('dis_discounts', function (Blueprint $table) {
                $table->dropColumn('mail_subject');
                $table->dropColumn('mail_content');
            });
        }

        if (Schema::hasColumns('usr_customer_coupon', [
            'mail_subject',
            'mail_content',
            'mail_sended_at',
        ])) {
            Schema::table('usr_customer_coupon', function (Blueprint $table) {
                $table->dropColumn('mail_subject');
                $table->dropColumn('mail_content');
                $table->dropColumn('mail_sended_at');
            });
        }
    }
}
