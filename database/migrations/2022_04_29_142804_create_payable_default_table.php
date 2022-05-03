<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayableDefaultTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_payable_default', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('項目名稱，用來設計「預設會計科目」的項目，例如：現金、支票、匯款、應付帳款、其它、外匯、商品、物流費用');
            $table->unsignedBigInteger('default_grade_id')->comment('會計科目預設值，對應到acc_all_grades table的primary key');
            $table->timestamps();
        });


        Schema::table('acc_currency', function (Blueprint $table) {
            $table->after('rate', function ($tb) {
                $tb->unsignedBigInteger('payable_default_fk')->nullable()->unique()->comment('付款單的會計科目預設值acc_payable_default foreign key');
            });
        });

        Schema::table('acc_income_expenditure', function (Blueprint $table) {
            $table->dropForeign(['acc_income_type_fk']);
            $table->dropColumn('acc_income_type_fk');

            $table->dropForeign(['acc_currency_fk']);
            $table->dropColumn('acc_currency_fk');
        });

        Schema::dropIfExists('acc_grade_default');

        Schema::dropIfExists('acc_income_expenditure');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('acc_payable_default');
    }
}
