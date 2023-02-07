<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCodeColumnToB2eCompanyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('usr_customers', function (Blueprint $table) {
            $table->after('latest_order', function ($tb) {
                $tb->integer('b2e_company_id')->nullable()->comment('企業id');
                $tb->dateTime('join_b2e_at')->nullable()->comment('加入企業時間');
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
        Schema::table('b2e_company', function (Blueprint $table) {
            //
        });
    }
}
