<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnInDlvLogisticTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dlv_logistic', function (Blueprint $table) {
            $table->after('memo', function ($tb) {
                $tb->string('ro_note')->nullable()->comment('備註');
                $tb->string('po_note')->nullable()->comment('備註');
            });
        });

        Schema::table('pcs_purchase', function (Blueprint $table) {
            $table->after('logistics_memo', function ($tb) {
                $tb->string('logistics_ro_note')->nullable()->comment('備註');
                $tb->string('logistics_po_note')->nullable()->comment('備註');
            });
        });

        Schema::table('pcs_purchase_items', function (Blueprint $table) {
            $table->after('memo', function ($tb) {
                $tb->string('ro_note')->nullable()->comment('備註');
                $tb->string('po_note')->nullable()->comment('備註');
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
        if (Schema::hasColumns('dlv_logistic', ['ro_note', 'po_note'])) {
            Schema::table('dlv_logistic', function (Blueprint $table) {
                $table->dropColumn('ro_note');
                $table->dropColumn('po_note');
            });
        }

        if (Schema::hasColumns('pcs_purchase', ['logistics_ro_note', 'logistics_po_note'])) {
            Schema::table('pcs_purchase', function (Blueprint $table) {
                $table->dropColumn('logistics_ro_note');
                $table->dropColumn('logistics_po_note');
            });
        }

        if (Schema::hasColumns('pcs_purchase_items', ['ro_note', 'po_note'])) {
            Schema::table('pcs_purchase_items', function (Blueprint $table) {
                $table->dropColumn('ro_note');
                $table->dropColumn('po_note');
            });
        }
    }
}
