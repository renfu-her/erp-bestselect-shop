<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

class ChangeErrcodeFieldInOrdPaymentCreditCardLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_income_orders', function (Blueprint $table) {
            $table->id();
            $table->string('sn')->nullable()->comment('入款單號');
            $table->decimal('amt_total_service_fee', 12, 2)->default(0)->comment('手續費總計');
            $table->decimal('amt_total_net', 12, 2)->default(0)->comment('入款金額總計');

            $table->unsignedBigInteger('service_fee_grade_id')->comment('信用卡手續費會計科目');
            $table->unsignedBigInteger('net_grade_id')->comment('信用卡入款會計科目');

            $table->dateTime('posting_date')->nullable()->comment('入款日期');

            $table->integer('creator_id')->comment('建立人員id');
            $table->integer('affirmant_id')->nullable()->comment('入款人員id');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('acc_received_credit', function (Blueprint $table) {
            $table->after('requested', function ($tb) {
                $tb->string('status_code', 10)->default(0)->comment('信用卡狀態');// 0:刷卡 1:請款 2:入款
                $tb->integer('income_order_id')->nullable()->comment('入款單id');
                $tb->string('sn')->nullable()->comment('入款單號');
                $tb->decimal('amt_percent', 12, 5)->default(1)->comment('請款比例');
                $tb->decimal('amt_service_fee', 12, 2)->default(0)->comment('手續費');
                $tb->decimal('amt_net', 12, 2)->default(0)->comment('入款金額');
                $tb->dateTime('transaction_date')->nullable()->comment('請款日期');
                $tb->dateTime('posting_date')->nullable()->comment('入款日期');

                $tb->dropColumn('requested');
            });

            $table->after('authamt', function ($tb) {
                $tb->dateTime('checkout_date')->nullable()->comment('刷卡日期');
                $tb->dropColumn('ckeckout_date');
            });
        });

        if (Schema::hasColumn('acc_received_credit', 'checkout_date'))
        {
            DB::statement('UPDATE acc_received_credit SET checkout_date = created_at');
        }


        Schema::table('ord_payment_credit_card_log', function (Blueprint $table) {
            $table->string('errcode', 10)->nullable()->comment('交易錯誤代碼')->change();

            $table->after('requested', function ($tb) {
                $tb->string('status_code', 10)->default(0)->comment('信用卡狀態');// 0:刷卡 1:請款 2:入款

                $tb->dropColumn('requested');
            });

            $table->after('installment', function ($tb) {
                $tb->dateTime('checkout_date')->nullable()->comment('刷卡日期');
                $tb->dropColumn('ckeckout_date');
            });
        });

        if (Schema::hasColumn('ord_payment_credit_card_log', 'checkout_date'))
        {
            DB::statement('UPDATE ord_payment_credit_card_log SET checkout_date = created_at');
        }


        Schema::table('crd_percent_bank_credit', function (Blueprint $table) {
			$table->decimal('percent', 12, 5)->default(0.982)->comment('請款比例')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('acc_income_orders');

        Schema::table('acc_received_credit', function (Blueprint $table) {
            $table->dropColumn('status_code');
            $table->dropColumn('income_order_id');
            $table->dropColumn('sn');
            $table->dropColumn('amt_percent');
            $table->dropColumn('amt_service_fee');
            $table->dropColumn('amt_net');
            $table->dropColumn('transaction_date');
            $table->dropColumn('posting_date');

            $table->after('installment', function ($tb) {
                $tb->string('requested', 10)->nullable()->default('n')->comment('是否請款');
            });

            $table->after('authamt', function ($tb) {
                $tb->dateTime('ckeckout_date')->nullable()->comment('刷卡日期');
                $tb->dropColumn('checkout_date');
            });
        });

        if (Schema::hasColumn('acc_received_credit', 'ckeckout_date'))
        {
            DB::statement('UPDATE acc_received_credit SET ckeckout_date = created_at');
        }


        Schema::table('ord_payment_credit_card_log', function (Blueprint $table) {
            $table->integer('errcode')->nullable()->comment('交易錯誤代碼')->change();

            $table->dropColumn('status_code');

            $table->after('checkout_area', function ($tb) {
                $tb->string('requested', 10)->nullable()->default('n')->comment('是否請款');
            });

            $table->after('installment', function ($tb) {
                $tb->dateTime('ckeckout_date')->nullable()->comment('刷卡日期');
                $tb->dropColumn('checkout_date');
            });
        });

        if (Schema::hasColumn('ord_payment_credit_card_log', 'ckeckout_date'))
        {
            DB::statement('UPDATE ord_payment_credit_card_log SET ckeckout_date = created_at');
        }


        Schema::table('crd_percent_bank_credit', function (Blueprint $table) {
			$table->decimal('percent')->default(1)->comment('請款比例')->change();
        });
    }
}
