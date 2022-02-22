<?php

namespace Database\Seeders;

use App\Models\FirstGrade;
use App\Models\IncomeStatement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('acc_company')->insert(['company' => '喜鴻國際有限公司']);

        IncomeStatement::create(['name' => '營業收入']);
        IncomeStatement::create(['name' => '營業成本']);
        IncomeStatement::create(['name' => '營業費用']);
        IncomeStatement::create(['name' => '非營業費用']);
        IncomeStatement::create(['name' => '非營業收入']);

        $firstGradeId_1 = FirstGrade::create([
            'has_next_grade' => 1,
            'name' => '資產',
            'acc_company_fk' => '1',
            'acc_income_statement_fk' => '1'
            ])->id;
        $firstGradeId_2 = FirstGrade::create([
            'has_next_grade' => 1,
            'name' => '負債',
            'acc_company_fk' => '1',
            'acc_income_statement_fk' => '1'
            ])->id;
        FirstGrade::create([
            'has_next_grade' => 1,
            'name' => '股東權益',
            'acc_company_fk' => '1',
            'acc_income_statement_fk' => '1'
            ]);
        FirstGrade::create([
            'has_next_grade' => 0,
            'name' => '股東收益',
            'acc_company_fk' => '1',
            'acc_income_statement_fk' => '1'
            ]);
        FirstGrade::create([
            'has_next_grade' => 1,
            'name' => '股東費用',
            'acc_company_fk' => '1',
            'acc_income_statement_fk' => '1'
            ]);

        $secondGradeId_1 = DB::table('acc_second_grade')->insertGetId([
            'code' => '11',
            'has_next_grade' => 1,
            'name' => '流動資產',
            'acc_company_fk' => 1,
            'first_grade_fk' => $firstGradeId_1,
            'acc_income_statement_fk' => 1
        ]);
        $secondGradeId_2 = DB::table('acc_second_grade')->insertGetId([
            'code' => '12',
            'has_next_grade' => 1,
            'name' => '固定資產',
            'acc_company_fk' => 1,
            'first_grade_fk' => $firstGradeId_1,
            'acc_income_statement_fk' => 1
        ]);
        $secondGradeId_3 = DB::table('acc_second_grade')->insertGetId([
            'code' => '21',
            'has_next_grade' => 1,
            'name' => '流動負債',
//            'acc_company_fk' => 1,
            'first_grade_fk' => $firstGradeId_2,
//            'acc_income_statement_fk' => 1
        ]);
        $secondGradeId_4 = DB::table('acc_second_grade')->insertGetId([
            'code' => '22',
            'has_next_grade' => 0,
            'name' => '長期負債',
            'acc_company_fk' => 1,
            'first_grade_fk' => $firstGradeId_2,
            'acc_income_statement_fk' => 1
        ]);

        $thirdGradeId_1 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1101',
            'has_next_grade' => 0,
            'name' => '現金',
            'acc_company_fk' => 1,
            'second_grade_fk' => $secondGradeId_1,
            'acc_income_statement_fk' => 1
        ]);
        $thirdGradeId_2 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1102',
            'has_next_grade' => 1,
            'name' => '銀行存款',
            'acc_company_fk' => 1,
            'second_grade_fk' => $secondGradeId_1,
            'acc_income_statement_fk' => 1
        ]);
        $thirdGradeId_3 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1201',
            'has_next_grade' => 0,
            'name' => '生財器具',
            'acc_company_fk' => 1,
            'second_grade_fk' => $secondGradeId_2,
            'acc_income_statement_fk' => 1
        ]);
        $thirdGradeId_4 = DB::table('acc_third_grade')->insertGetId([
            'code' => '2101',
            'has_next_grade' => 0,
            'name' => '應付票據',
            'acc_company_fk' => 1,
            'second_grade_fk' => $secondGradeId_3,
            'acc_income_statement_fk' => 1
        ]);
        $thirdGradeId_5 = DB::table('acc_third_grade')->insertGetId([
            'code' => '2102',
            'has_next_grade' => 0,
            'name' => '應付帳款',
            'acc_company_fk' => 1,
            'second_grade_fk' => $secondGradeId_3,
            'acc_income_statement_fk' => 1
        ]);

        DB::table('acc_fourth_grade')->insert([
            'code' => '11020001',
            'name' => '銀行存款-合庫長春公司戶A',
            'acc_company_fk' => 1,
            'third_grade_fk' => $thirdGradeId_2,
            'acc_income_statement_fk' => 1,
        ]);
        DB::table('acc_fourth_grade')->insert([
            'code' => '11020002',
            'name' => '銀行存款-合庫長春公司戶B',
            'acc_company_fk' => 1,
            'third_grade_fk' => $thirdGradeId_2,
            'acc_income_statement_fk' => 1,
        ]);
        DB::table('acc_fourth_grade')->insert([
            'code' => '21020001',
            'name' => '應付帳款-其他',
            'acc_company_fk' => 1,
            'third_grade_fk' => $thirdGradeId_4,
            'acc_income_statement_fk' => 1,
            'note_1' => '2014/8/31以前應付帳款轉用'
        ]);
        DB::table('acc_fourth_grade')->insert([
            'code' => '21020002',
            'name' => '應付帳款-茶衣創意',
            'acc_company_fk' => 1,
            'third_grade_fk' => $thirdGradeId_4,
            'acc_income_statement_fk' => 1,
        ]);
    }
}
