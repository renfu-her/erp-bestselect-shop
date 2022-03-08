<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 優惠類別 全館 vip...
        Schema::create('dis_act_categorys', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable()->comment('名稱');
            $table->string('code')->nullable()->comment('代碼');
            $table->integer('sort')->nullable()->comment('排序');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['code']);
        });

        // 優惠方式 金額 百分比
        Schema::create('dis_discount_types', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('名稱');
            $table->string('code')->comment('代碼');
            $table->unique(['code']);
        });

        // 一般優惠 優惠券 優惠代碼
        Schema::create('dis_categorys', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable()->comment('名稱');
            $table->string('code')->nullable()->comment('代碼');
            $table->integer('sort')->nullable()->comment('排序');

            $table->unique(['code']);
        });

        Schema::create('dis_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable()->comment('名稱');
            $table->string('category_code')->nullable()->comment('代碼');
            $table->string('discount_type_code')->nullable()->comment('代碼');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['code']);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dis_categorys');
        Schema::dropIfExists('dis_discounts');
        Schema::dropIfExists('dis_act_categorys');
        Schema::dropIfExists('dis_discount_types');

    }
}
