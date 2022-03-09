<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncomeExpenditureTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_income_type', function (Blueprint $table) {
            $table->id()->comment('入款方式');
            $table->string('type')->comment('入款方式');
            $table->integer('grade')->comment('1~4級科目');
            $table->timestamps();
        });

        Schema::create('acc_currency', function (Blueprint $table) {
            $table->id()->comment('外幣');
            $table->string('name')->unique()->comment('外幣名稱');
            $table->decimal('rate')->comment('外幣匯率');
        });

        Schema::create('acc_income_expenditure', function (Blueprint $table) {
            $table->id()->comment('收支科目設定');

            $table->unsignedBigInteger('acc_income_type_fk')->default(null)->comment('入款方式, foreign key');
            $table->foreign('acc_income_type_fk')->references('id')->on('acc_income_type');

            $table->integer('grade_id_fk')->nullable()->comment('對應到1～4級科目table的primary key');

            $table->unsignedBigInteger('acc_currency_fk')->nullable()->unique()->default(null)->comment('外幣, foreign key');
            $table->foreign('acc_currency_fk')->references('id')->on('acc_currency');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('acc_income_expenditure', function (Blueprint $table) {
            $table->dropForeign(['acc_income_type_fk']);
            $table->dropColumn('acc_income_type_fk');

            $table->dropForeign(['acc_currency_fk']);
            $table->dropColumn('acc_currency_fk');
        });

        Schema::dropIfExists('acc_income_type');
        Schema::dropIfExists('acc_currency');
        Schema::dropIfExists('acc_income_expenditure');
    }
}
