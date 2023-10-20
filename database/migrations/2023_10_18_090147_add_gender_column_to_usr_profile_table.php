<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGenderColumnToUsrProfileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usr_profile', function (Blueprint $table) {
            //
            $table->after('history', function ($tb) {
                $tb->string('gender', 20)->nullable()->comment('性別');
                $tb->string('education_training')->nullable()->comment('教育訓練');

                $tb->string('labor_contract', 20)->nullable()->comment('勞動契約');
                $tb->string('undertake_contract', 20)->nullable()->comment('承攬契約');
                $tb->integer('labor_insurance_retire')->nullable()->comment('勞退投保金額');
                $tb->integer('labor_insurance_self')->nullable()->comment('自行提撥');
                $tb->string('jp_phone')->nullable()->comment('日本手機');
                $tb->string('manager_certificate', 20)->nullable()->comment('經理證');
                $tb->string('leader_certificate', 20)->nullable()->comment('領隊證');
                $tb->date('leader_certificate_start')->nullable()->comment('領隊證領取日');
                $tb->date('leader_certificate_end')->nullable()->comment('領隊證有效日');
                $tb->date('leader_certificate_correction')->nullable()->comment('領隊證校正日');
                $tb->string('leader_language')->nullable()->comment('領隊語言別');
                $tb->string('special_person', 20)->nullable()->comment('特殊人士');
                $tb->string('disability_certificate', 20)->nullable()->comment('領有身心障礙手冊');
                $tb->integer('travel_service_year')->nullable()->comment('旅行社服務年資');
                $tb->integer('non_travel_service_year')->nullable()->comment('非旅行社服務年資');

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
        Schema::table('usr_profile', function (Blueprint $table) {
            //
        });
    }
}
