<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_balance_sheet', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128)->unique()->comment('會計分類名稱');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('acc_income_statement', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128)->unique()->comment('科目類別名稱');
            $table->softDeletes();
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
        Schema::dropIfExists('acc_balance_sheet');
        Schema::dropIfExists('acc_income_statement');
    }
}
