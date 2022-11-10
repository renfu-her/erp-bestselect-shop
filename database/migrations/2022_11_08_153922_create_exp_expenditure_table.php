<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpExpenditureTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exp_expenditure', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('sn');
            $table->string('title')->comment("主旨");
            $table->text('content')->nullable();
            $table->integer('department_id')->comment('支出單位');
            $table->integer('item_id')->comment('支出科目');
            $table->integer('payment_id')->comment('支付方式');
            $table->integer('amount')->comment('金額');

            
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('exp_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
        });

        Schema::create('exp_payment', function (Blueprint $table) {
            $table->id();
            $table->string('title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exp_expenditure');
        Schema::dropIfExists('exp_items');
        Schema::dropIfExists('exp_payment');

    }
}
