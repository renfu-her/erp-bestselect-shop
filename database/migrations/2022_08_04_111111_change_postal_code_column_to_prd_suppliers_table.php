<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePostalCodeColumnToPrdSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prd_suppliers', function (Blueprint $table) {
            $table->string('postal_code', 10)->comment('公司郵遞區號')->change();
            $table->string('invoice_postal_code', 10)->nullable()->comment('發票郵遞區號')->change();
            $table->string('shipping_postal_code', 10)->nullable()->comment('收貨郵遞區號')->change();
        });
        Schema::table('ord_address', function (Blueprint $table) {
            $table->string('zipcode', 10)->comment('郵遞區號')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prd_suppliers', function (Blueprint $table) {
            $table->unsignedInteger('postal_code')->comment('公司郵遞區號')->change();
            $table->unsignedInteger('invoice_postal_code')->nullable()->comment('發票郵遞區號')->change();
            $table->unsignedInteger('shipping_postal_code')->nullable()->comment('收貨郵遞區號')->change();
        });
        Schema::table('ord_address', function (Blueprint $table) {
            $table->string('zipcode', 5)->comment('郵遞區號')->change();
        });
    }
}
