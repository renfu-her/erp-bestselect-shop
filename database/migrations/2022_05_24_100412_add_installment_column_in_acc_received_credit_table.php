<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInstallmentColumnInAccReceivedCreditTable extends Migration
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
                $tb->string('installment', 10)->default('none')->comment('信用卡分期數');
            });
        });

        if (Schema::hasColumns('acc_received', ['accountant_id_fk'])) {
            Schema::table('acc_received', function (Blueprint $table) {
                // $table->dropForeign(['accountant_id_fk']);
                // $table->dropIndex('acc_received_accountant_id_fk_foreign');
            });
        }

        if (Schema::hasColumns('acc_payable', ['accountant_id_fk'])) {
            Schema::table('acc_payable', function (Blueprint $table) {
                // $table->dropForeign(['accountant_id_fk']);
                // $table->dropIndex('acc_payable_accountant_id_fk_foreign');
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
        Schema::table('acc_received_credit', function (Blueprint $table) {
            $table->dropColumn('installment');
        });
    }
}
