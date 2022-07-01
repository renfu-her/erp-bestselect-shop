<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInstallmentColumnInCrdBanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crd_banks', function (Blueprint $table) {
            $table->after('grade_fk', function ($tb) {
                $tb->string('installment', 10)->default('none')->comment('信用卡分期數');
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
        Schema::table('crd_banks', function (Blueprint $table) {
            $table->dropColumn('installment');
        });
    }
}
