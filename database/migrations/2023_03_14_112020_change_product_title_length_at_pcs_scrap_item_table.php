<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeProductTitleLengthAtPcsScrapItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pcs_scrap_item', function (Blueprint $table) {
            $table->string('product_title', 100)->nullable()->comment('商品名稱')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pcs_scraps', function (Blueprint $table) {
            $table->renameColumn('audit_status', 'status');
        });
    }
}
