<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRNoteColumnInAccReceivedRefundTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('acc_received_refund', function (Blueprint $table) {
            $table->after('note', function ($tb) {
                $tb->string('ro_note')->nullable()->comment('收款單品項備註');
                $tb->string('po_note')->nullable()->comment('付款單品項備註');
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
        if (Schema::hasColumns('acc_received_refund', ['ro_note', 'po_note'])) {
            Schema::table('acc_received_refund', function (Blueprint $table) {
                $table->dropColumn('ro_note');
                $table->dropColumn('po_note');
            });
        }
    }
}
