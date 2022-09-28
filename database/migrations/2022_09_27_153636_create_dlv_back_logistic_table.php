<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDlvBackLogisticTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dlv_back_items', function (Blueprint $table) {
            $table->id()->comment('退貨物流id ');
            $table->unsignedBigInteger('delivery_id')->comment('出貨單id');
            $table->tinyInteger('type')->comment('類別 1:物流 2:銷貨收入');
            $table->string('title')->nullable()->comment('名稱');
            $table->decimal('price', 12, 2)->nullable()->comment('金額');
            $table->integer('qty')->nullable()->comment('數量');
            $table->string('memo')->nullable()->comment('備註');
            $table->timestamps();
        });

        Schema::table('dlv_back', function (Blueprint $table) {
            $table->after('updated_at', function ($tb) {
                $tb->tinyInteger('show')->default(0)->comment('是否揭示 0:否 1:是');
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dlv_back_items');

        if (Schema::hasColumns('dlv_back', [
            'show',
        ])) {
            Schema::table('dlv_back', function (Blueprint $table) {
                $table->dropColumn('show');
            });
        }
    }
}
