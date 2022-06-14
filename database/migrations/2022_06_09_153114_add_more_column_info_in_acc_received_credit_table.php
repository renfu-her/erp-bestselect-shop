<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

class AddMoreColumnInfoInAccReceivedCreditTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('acc_received_credit', function (Blueprint $table) {
            $table->after('id', function ($tb) {
                $tb->string('cardnumber')->nullable()->comment('隱碼卡號');
                $tb->decimal('authamt', 12, 2)->nullable()->default(0)->comment('交易金額');
                $tb->dateTime('ckeckout_date')->nullable()->comment('刷卡日期');
                $tb->string('card_type_code', 50)->nullable()->comment('信用卡別 key');// visa,master...
                $tb->string('card_type', 50)->nullable()->comment('信用卡別');// visa,master...
                $tb->string('card_owner_name')->nullable()->comment('持卡人姓名');
                $tb->string('authcode')->nullable()->comment('交易授權碼');
                $tb->unsignedBigInteger('all_grades_id')->comment('會計科目');// achieve
                $tb->string('checkout_area_code', 100)->nullable()->comment('結帳地區');
                $tb->string('checkout_area', 100)->nullable()->comment('結帳地區');
            });

            $table->after('installment', function ($tb) {
                $tb->string('requested', 10)->nullable()->default('n')->comment('是否請款');// n / y
                $tb->string('card_nat', 20)->nullable()->default('local')->comment('國內外信用卡');// local / foreign
                $tb->string('checkout_mode', 50)->nullable()->default('online')->comment('線上或線下');// online / offline
            });
        });

        if (Schema::hasColumn('acc_received_credit', 'ckeckout_date'))
        {
            DB::statement('UPDATE acc_received_credit SET ckeckout_date = created_at');
        }

        Schema::table('ord_payment_credit_card_log', function (Blueprint $table) {
            $table->after('authresurl', function ($tb) {
                $tb->string('installment', 10)->default('none')->comment('信用卡分期數');
                $tb->dateTime('ckeckout_date')->nullable()->comment('刷卡日期');
                $tb->string('card_type_code', 50)->nullable()->comment('信用卡別 key');// visa,master...
                $tb->string('card_type', 50)->nullable()->comment('信用卡別');// visa,master...
                $tb->string('card_owner_name')->nullable()->comment('持卡人姓名');
                $tb->unsignedBigInteger('all_grades_id')->comment('會計科目');// achieve
                $tb->string('checkout_area_code', 100)->nullable()->comment('結帳地區');
                $tb->string('checkout_area', 100)->nullable()->comment('結帳地區');
                $tb->string('requested', 10)->nullable()->default('n')->comment('是否請款');// n / y
                $tb->string('card_nat', 20)->nullable()->default('local')->comment('國內外信用卡');// local / foreign
                $tb->string('checkout_mode', 50)->nullable()->default('online')->comment('線上或線下');// online / offline
            });
        });

        if (Schema::hasColumn('ord_payment_credit_card_log', 'ckeckout_date'))
        {
            DB::statement('UPDATE ord_payment_credit_card_log SET ckeckout_date = created_at');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('acc_received_credit', function (Blueprint $table) {
            $table->dropColumn('cardnumber');
            $table->dropColumn('authamt');
            $table->dropColumn('ckeckout_date');
            $table->dropColumn('card_type_code');
            $table->dropColumn('card_type');
            $table->dropColumn('card_owner_name');
            $table->dropColumn('authcode');
            $table->dropColumn('all_grades_id');
            $table->dropColumn('checkout_area_code');
            $table->dropColumn('checkout_area');
            $table->dropColumn('requested');
            $table->dropColumn('card_nat');
            $table->dropColumn('checkout_mode');
        });


        Schema::table('ord_payment_credit_card_log', function (Blueprint $table) {
            $table->dropColumn('installment');
            $table->dropColumn('ckeckout_date');
            $table->dropColumn('card_type_code');
            $table->dropColumn('card_type');
            $table->dropColumn('card_owner_name');
            $table->dropColumn('all_grades_id');
            $table->dropColumn('checkout_area_code');
            $table->dropColumn('checkout_area');
            $table->dropColumn('requested');
            $table->dropColumn('card_nat');
            $table->dropColumn('checkout_mode');
        });
    }
}
