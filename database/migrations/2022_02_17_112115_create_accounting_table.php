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
        Schema::create('acc_company', function (Blueprint $table) {
            $table->id()->comment('公司');
            $table->string('company')->unique()->comment('公司名');
        });

        Schema::create('acc_income_statement', function (Blueprint $table) {
            $table->id()->comment('科目類別');
            $table->string('name', 128)->unique()->comment('科目類別名稱');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('acc_first_grade', function (Blueprint $table) {
            $table->id()->comment('會計分類（一級科目）');
            $table->boolean('has_next_grade')->comment('有無「子科目」（二級科目）? 1:有, 0:無');
            $table->string('name', 128)->unique()->comment('會計分類名稱');

            $table->unsignedBigInteger('acc_company_fk')->nullable()->default(null)->comment('公司, foreign key');
            $table->foreign('acc_company_fk')->references('id')->on('acc_company');

            $table->unsignedBigInteger('income_statement_fk')->nullable()->default(null)->comment('科目類別, foreign key');
            $table->foreign('income_statement_fk')->references('id')->on('acc_income_statement');

            $table->softDeletes();

            $table->timestamps();
        });

//        Schema::create('acc_first_grade', function (Blueprint $table) {
//            $table->id()->comment('一級科目');
//            $table->string('name', 128)->unique()->comment('一級科目名稱');
//            $table->softDeletes();
//            $table->timestamps();
//        });

        Schema::create('acc_second_grade', function (Blueprint $table) {
            $table->id()->comment('子科目（二級科目）');
            $table->string('code')->unique()->comment('科目代碼');
            $table->boolean('has_next_grade')->comment('有無「子次科目」（三級科目）? 1:有, 0:無');
            $table->string('name', 128)->unique()->comment('子科目（二級科目）名稱');

            $table->unsignedBigInteger('acc_company_fk')->nullable()->default(null)->comment('公司, foreign key');
            $table->foreign('acc_company_fk')->references('id')->on('acc_company');

            $table->unsignedBigInteger('first_grade_fk')->comment('會計分類（一級科目）, foreign key');
            $table->foreign('first_grade_fk')->references('id')->on('acc_first_grade');

            $table->unsignedBigInteger('income_statement_fk')->nullable()->default(null)->comment('科目類別, foreign key');
            $table->foreign('income_statement_fk')->references('id')->on('acc_income_statement');

            $table->string('note_1')->comment('備註一');
            $table->string('note_2')->comment('備註二');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('acc_third_grade', function (Blueprint $table) {
            $table->id()->comment('子次科目（三級科目）');
            $table->string('code')->unique()->comment('科目代碼');
            $table->boolean('has_next_grade')->comment('有無「子底科目」（四級科目）? 1:有, 0:無');
            $table->string('name', 128)->unique()->comment('子次科目（三級科目）名稱');

            $table->unsignedBigInteger('acc_company_fk')->nullable()->default(null)->comment('公司, foreign key');
            $table->foreign('acc_company_fk')->references('id')->on('acc_company');

            $table->unsignedBigInteger('second_grade_fk')->comment('子科目（二級科目）, foreign key');
            $table->foreign('second_grade_fk')->references('id')->on('acc_second_grade');

            $table->unsignedBigInteger('income_statement_fk')->nullable()->default(null)->comment('科目類別, foreign key');
            $table->foreign('income_statement_fk')->references('id')->on('acc_income_statement');

            $table->string('note_1')->comment('備註一');
            $table->string('note_2')->comment('備註二');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('acc_fourth_grade', function (Blueprint $table) {
            $table->id()->comment('子底科目（四級科目）');
            $table->string('code')->unique()->comment('科目代碼');
            $table->string('name', 128)->unique()->comment('子底科目（四級科目）名稱');

            $table->unsignedBigInteger('acc_company_fk')->nullable()->default(null)->comment('公司, foreign key');
            $table->foreign('acc_company_fk')->references('id')->on('acc_company');

            $table->unsignedBigInteger('third_grade_fk')->comment('子次科目（三級科目）, foreign key');
            $table->foreign('third_grade_fk')->references('id')->on('acc_third_grade');

            $table->unsignedBigInteger('income_statement_fk')->nullable()->default(null)->comment('科目類別, foreign key');
            $table->foreign('income_statement_fk')->references('id')->on('acc_income_statement');

            $table->string('note_1')->comment('備註一');
            $table->string('note_2')->comment('備註二');
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
        Schema::table('acc_first_grade', function (Blueprint $table) {
            $table->dropForeign(['acc_company_fk']);
            $table->dropColumn('acc_company_fk');
        });
        Schema::table('acc_second_grade', function (Blueprint $table) {
            $table->dropForeign(['acc_company_fk']);
            $table->dropColumn('acc_company_fk');
        });
        Schema::table('acc_third_grade', function (Blueprint $table) {
            $table->dropForeign(['acc_company_fk']);
            $table->dropColumn('acc_company_fk');
        });
        Schema::table('acc_fourth_grade', function (Blueprint $table) {
            $table->dropForeign(['acc_company_fk']);
            $table->dropColumn('acc_company_fk');
        });
        Schema::dropIfExists('acc_company');

        Schema::table('acc_fourth_grade', function (Blueprint $table) {
            $table->dropForeign(['third_grade_fk']);
            $table->dropColumn('third_grade_fk');
        });
        Schema::table('acc_third_grade', function (Blueprint $table) {
            $table->dropForeign(['second_grade_fk']);
            $table->dropColumn('second_grade_fk');
        });
        Schema::table('acc_second_grade', function (Blueprint $table) {
            $table->dropForeign(['first_grade_fk']);
            $table->dropColumn('first_grade_fk');
        });

        Schema::table('acc_fourth_grade', function (Blueprint $table) {
            $table->dropForeign(['income_statement_fk']);
            $table->dropColumn('income_statement_fk');
        });
        Schema::table('acc_third_grade', function (Blueprint $table) {
            $table->dropForeign(['income_statement_fk']);
            $table->dropColumn('income_statement_fk');
        });
        Schema::table('acc_second_grade', function (Blueprint $table) {
            $table->dropForeign(['income_statement_fk']);
            $table->dropColumn('income_statement_fk');
        });

        Schema::dropIfExists('acc_fourth_grade');
        Schema::dropIfExists('acc_third_grade');
        Schema::dropIfExists('acc_second_grade');

        Schema::dropIfExists('acc_first_grade');
        Schema::dropIfExists('acc_income_statement');
    }
}
