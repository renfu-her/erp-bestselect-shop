<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoteColumnInDlvBackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dlv_back', function (Blueprint $table) {
            $table->after('memo', function ($tb) {
                $tb->string('ro_note')->nullable()->comment('收款單品項備註');
                $tb->string('po_note')->nullable()->comment('付款單品項備註');
            });
        });

        Schema::table('dlv_out_stock', function (Blueprint $table) {
            $table->after('memo', function ($tb) {
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
        if (Schema::hasColumns('dlv_back', ['ro_note', 'po_note'])) {
            Schema::table('dlv_back', function (Blueprint $table) {
                $table->dropColumn('ro_note');
                $table->dropColumn('po_note');
            });
        }

        if (Schema::hasColumns('dlv_out_stock', ['ro_note', 'po_note'])) {
            Schema::table('dlv_out_stock', function (Blueprint $table) {
                $table->dropColumn('ro_note');
                $table->dropColumn('po_note');
            });
        }
    }
}
