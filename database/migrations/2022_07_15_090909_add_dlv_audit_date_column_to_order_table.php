<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDlvAuditDateColumnToOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ord_orders', function (Blueprint $table) {
            $table->after('invoice_number', function ($tb) {
                $tb->dateTime('dlv_audit_date')->nullable()->comment('出貨審核日期');
            });
        });

        Schema::table('csn_consignment', function (Blueprint $table) {
            $table->after('memo', function ($tb) {
                $tb->dateTime('dlv_audit_date')->nullable()->comment('出貨審核日期');
            });
        });

        Schema::table('csn_orders', function (Blueprint $table) {
            $table->after('memo', function ($tb) {
                $tb->dateTime('dlv_audit_date')->nullable()->comment('出貨審核日期');
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
        if (Schema::hasColumns('ord_orders', ['dlv_audit_date',])) {
            Schema::table('ord_orders', function (Blueprint $table) {
                $table->dropColumn('dlv_audit_date');
            });
        }
        if (Schema::hasColumns('csn_consignment', ['dlv_audit_date',])) {
            Schema::table('csn_consignment', function (Blueprint $table) {
                $table->dropColumn('dlv_audit_date');
            });
        }
        if (Schema::hasColumns('csn_orders', ['dlv_audit_date',])) {
            Schema::table('csn_orders', function (Blueprint $table) {
                $table->dropColumn('dlv_audit_date');
            });
        }
    }
}
