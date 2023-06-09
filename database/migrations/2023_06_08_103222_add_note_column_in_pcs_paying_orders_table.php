<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoteColumnInPcsPayingOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pcs_paying_orders', function (Blueprint $table) {
            $table->after('memo', function ($tb) {
                $tb->string('ro_note')->nullable()->comment('收款單品項備註');
                $tb->string('po_note')->nullable()->comment('付款單品項備註');
            });
        });

        Schema::table('pcs_purchase_items', function (Blueprint $table) {
            $table->decimal('price', 15, 4)->comment('總價')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumns('pcs_paying_orders', ['ro_note', 'po_note'])) {
            Schema::table('pcs_paying_orders', function (Blueprint $table) {
                $table->dropColumn('ro_note');
                $table->dropColumn('po_note');
            });
        }
    }
}
