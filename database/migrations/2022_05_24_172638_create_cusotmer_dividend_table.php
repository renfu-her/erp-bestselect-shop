<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCusotmerDividendTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_cusotmer_dividend', function (Blueprint $table) {
            $table->id();
            $table->string('category')->comment('來源類型')->nullable();
            $table->string('category_sn')->comment('來源sn或id')->nullable();
            $table->integer('customer_id')->comment('會員id');
            $table->string('type')->comment('獲得/消耗');
            $table->string('flag')->comment('行為');
            $table->string('flag_title')->comment('行為title');
            $table->integer('weight')->comment('權重')->default(50); //
            $table->integer('dividend')->comment('點數'); //有期限 無期限
            $table->integer('used_dividend')->default(0)->comment('以使用點數'); //有期限 無期限
            $table->tinyInteger('deadline')->default(1)->comment('是否有期限1有0沒有'); //有期限 無期限

            
         //   $table->string('flag')->comment('flag')->nullable(); // 註銷etc
            $table->dateTime('active_sdate')->nullable()->comment('生效時間起');
            $table->dateTime('active_edate')->nullable()->comment('生效時間迄');
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
        Schema::dropIfExists('usr_cusotmer_dividend');
    }
}
