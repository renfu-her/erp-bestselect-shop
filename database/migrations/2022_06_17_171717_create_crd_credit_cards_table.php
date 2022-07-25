<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrdCreditCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crd_credit_cards', function (Blueprint $table) {
            $table->id()->comment('信用卡ID');
            $table->string('title', 30)->comment('名稱');
            $table->softDeletes();
        });
        Schema::create('crd_banks', function (Blueprint $table) {
            $table->id()->comment('銀行ID');
            $table->string('title', 30)->comment('名稱');
            $table->string('bank_code', 30)->nullable()->default(null)->comment('傳票代碼');
            $table->unsignedBigInteger('grade_fk')->comment('對應到acc_all_grades的primary key');
            $table->foreign('grade_fk')->references('id')->on('acc_all_grades');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('crd_percent_bank_credit', function (Blueprint $table) {
            $table->id()->comment('銀行信用卡比例ID');
            $table->unsignedBigInteger('bank_fk')->comment('對應到crd_banks的primary key');
            $table->foreign('bank_fk')->references('id')->on('crd_banks');
            $table->unsignedBigInteger('credit_fk')->comment('對應到crd_credit_cards的primary key');
            $table->foreign('credit_fk')->references('id')->on('crd_credit_cards');
			$table->decimal('percent')->default(1)->comment('請款比例');
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
        Schema::table('crd_percent_bank_credit', function (Blueprint $table)
        {
            $table->dropForeign('crd_percent_bank_credit_bank_fk_foreign');
            $table->dropForeign('crd_percent_bank_credit_credit_fk_foreign');
        });
        Schema::dropIfExists('crd_credit_cards');
        Schema::dropIfExists('crd_banks');
        Schema::dropIfExists('crd_percent_bank_credit');
    }
}
