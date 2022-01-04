<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrdSupplierTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prd_suppliers', function (Blueprint $table) {
            $table->id()->comment('廠商');
            $table->string('name')->comment('廠商名稱');
            $table->string('nickname')->nullable()->comment('廠商簡稱');
            $table->string('vat_no', 8)->nullable()->comment('統編');
            $table->string('contact_tel')->comment('聯絡電話');
            $table->string('contact_address')->comment('聯絡地址');
            $table->string('contact_person')->comment('廠商窗口');
            $table->string('email')->comment('電子郵件');
            $table->string('memo')->nullable()->comment('備註');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prd_suppliers');
    }
}
