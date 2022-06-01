<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSupplierIdFkInShiGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shi_group', function (Blueprint $table) {
            $table->after('temps_fk', function ($tb) {
                $tb->unsignedBigInteger('supplier_fk')->nullable()->default(null)->comment('廠商 foreign key');
                $tb->foreign('supplier_fk')->references('id')->on('prd_suppliers');
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
        Schema::table('shi_group', function (Blueprint $table) {
            $table->dropColumn('supplier_fk');
        });
    }
}
