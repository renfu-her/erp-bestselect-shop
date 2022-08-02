<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csp_custom_pages', function (Blueprint $table) {
            $table->id()->comment('自訂頁面');
            $table->string('page_name')->unique()->comment('頁面名稱');
            $table->string('url')->unique()->comment('網頁連結名稱');
            $table->string('title')->unique()->comment('網頁標題');
            $table->string('desc')->comment('網頁描述');
            $table->string('link')->comment('複製用的URL連結');
            $table->unsignedTinyInteger('prd_sale_channels_id_fk')->comment('通路選擇, prd_sale_channels foreign key');
            $table->unsignedBigInteger('usr_users_id_fk')->comment('使用者, usr_users foreign key');

            $table->unsignedTinyInteger('type')->comment('自訂HTML類型  一般:1,活動頁:2');
            $table->unsignedBigInteger('csp_html_type_fk')->comment('對應到table csp_general_html, csp_activity_html的id foreign key');

            $table->timestamps();
        });

        Schema::create('csp_general_html', function (Blueprint $table) {
            $table->id()->comment('【一般】HTML自訂內容');
            $table->text('content')->nullable()->comment('HTML程式碼');
            $table->timestamps();
        });

        Schema::create('csp_activity_html', function (Blueprint $table) {
            $table->id()->comment('【活動頁】HTML自訂內容');
            $table->text('head')->nullable()->comment('Head 資訊');
            $table->text('body')->nullable()->comment('網頁內容程式碼');
            $table->text('script')->nullable()->comment('網頁內嵌 JavaScript 程式碼');
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
    }
}
