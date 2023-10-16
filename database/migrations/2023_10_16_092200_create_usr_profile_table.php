<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrProfileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_profile', function (Blueprint $table) {
            $table->integer('user_id')->unique()->comment('user id');
            $table->string('en_name')->nullable()->comment('英文名字');
            $table->string('identity')->nullable()->comment('身分證');
            $table->string('job_title')->nullable()->comment('職稱');
            $table->date('date_of_job_entry')->nullable()->comment('到職日');
            $table->date('date_of_job_leave')->nullable()->comment('離職日');
            $table->string('live_with_family', 20)->nullable()->comment('與家人同住');
            $table->string('performance_statistics', 20)->nullable()->comment('業績統計');
            $table->text('certificates')->nullable()->comment('證照專長');
            $table->string('disc_category', 100)->nullable()->comment('disc類型');
            $table->string('insurance_certification', 20)->nullable()->comment('保險認證');
            $table->string('ability_english')->nullable()->comment('英文能力');
            $table->string('ability_japanese')->nullable()->comment('日文能力');
            $table->string('english_certification')->nullable()->comment('英文證照');
            $table->string('japanese_certification')->nullable()->comment('日文證照');
            $table->date('date_of_insurance_entry')->nullable()->comment('到職日');
            $table->date('date_of_insurance_leave')->nullable()->comment('離職日');
            $table->integer('labor_insurance')->default(0)->comment('勞保金額');
            $table->integer('labor_insurance_oop')->default(0)->comment('勞保自付額');
            $table->integer('health_insurance')->default(0)->comment('健康保金額');
            $table->integer('health_insurance_oop')->default(0)->comment('健康保自付額');
            $table->integer('health_insurance_dependents')->default(0)->comment('健保眷屬');
            $table->string('tel')->nullable()->comment('聯繫電話');
            $table->string('phone')->nullable()->comment('手機');
            $table->string('address')->nullable()->comment('聯絡地址');
            $table->string('email')->nullable()->comment('email');

            $table->string('office_tel')->nullable()->comment('公司電話');
            $table->string('office_tel_ext')->nullable()->comment('公司電話分機');
            $table->string('office_fax')->nullable()->comment('公司傳真');
            $table->string('contact_person')->nullable()->comment('緊急聯絡人');
            $table->string('contact_person_tel')->nullable()->comment('緊急聯絡人電話');
            $table->string('service_area')->nullable()->comment('服務地區');
            $table->string('office_address')->nullable()->comment('公司地址');
            $table->date('birthday')->nullable()->comment('生日');
            $table->string('blood_type', 10)->nullable()->comment('血型');
            $table->string('household_address')->nullable()->comment('戶籍地址');

            $table->string('household_tel')->nullable()->comment('戶籍電話');
            $table->string('education')->nullable()->comment('最高學歷');
            $table->string('education_department')->nullable()->comment('科系');
            $table->string('punch_in', 20)->nullable()->comment('上班打卡');
            $table->string('note')->nullable()->comment('備註');
            $table->text('history')->nullable()->comment('資歷');
            $table->string('img')->nullable()->comment('照片');
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
        Schema::dropIfExists('usr_profile');
    }
}
