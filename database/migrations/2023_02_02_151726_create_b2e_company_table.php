<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateB2eCompanyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b2e_company', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('企業全名');
            $table->string('short_title')->comment('企業簡稱');
            $table->string('vat_no')->comment('統編');
            $table->string('code')->comment('驗證碼');
            $table->string('tel')->nullable()->comment('企業電話');
            $table->string('ext')->nullable()->comment('分機號碼');
            $table->string('contact_person')->comment('窗口');
            $table->string('contact_tel')->nullable()->comment('窗口手機');
            $table->string('contact_email')->nullable()->comment('窗口信箱');
            $table->date('contract_sdate')->comment('合約起始');
            $table->date('contract_edate')->comment('合約結束');
            $table->integer('salechannel_id')->comment('銷售通路');
            $table->integer('user_id')->nullable()->comment('業務員');
            $table->string('img')->nullable()->comment('logo');
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
        Schema::dropIfExists('b2e_company');
    }
}
