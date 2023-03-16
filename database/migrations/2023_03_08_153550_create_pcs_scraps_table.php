<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePcsScrapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pcs_scraps', function (Blueprint $table) {
            $table->id()->comment('報廢單ID');
            $table->string('type', 10)->comment('類別 PcsScrapType scrap:報廢');
            $table->string('sn', 20)->comment('單號');
            $table->unsignedBigInteger('user_id')->nullable()->comment('新增者');
            $table->string('user_name', 20)->nullable()->comment('新增者名稱');
            $table->unsignedBigInteger('audit_user_id')->nullable()->comment('審核者ID');
            $table->string('audit_user_name', 20)->nullable()->comment('審核者名稱');
            $table->string('memo')->nullable()->comment('備註');
            $table->string('status', 20)->nullable()->comment('報廢單狀態');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('pcs_scrap_item', function (Blueprint $table) {
            $table->id()->comment('報廢商品款式ID');
            $table->unsignedBigInteger('scrap_id')->comment('報廢單ID');
            $table->unsignedBigInteger('inbound_id')->nullable()->comment('入庫單ID');
            $table->integer('product_style_id')->nullable()->comment('款式ID');
            $table->string('sku', 20)->nullable()->comment('商品sku');
            $table->string('product_title', 40)->comment('商品名稱');
            $table->integer('price')->nullable()->comment('單價');
            $table->integer('qty')->nullable()->comment('數量');
            $table->string('memo')->nullable()->comment('備註');
            $table->timestamps();
            $table->softDeletes();
            $table->tinyInteger('type')->default(0)->comment('類別 0:商品 1:物流 2:銷貨收入');
            $table->unsignedBigInteger('grade_id')->comment('會計科目id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pcs_scraps');
        Schema::dropIfExists('pcs_scrap_item');
    }
}
