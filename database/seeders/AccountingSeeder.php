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
        DB::table('acc_company')->insert([
            'company' => '喜鴻國際有限公司',
            'address' => '台北市中山區松江路148號6樓之2',
            'phone' => '02-25637600',
            'fax' => '02-25711377'
        ]);

        IncomeStatement::create(['name' => '營業收入']);
        IncomeStatement::create(['name' => '營業成本']);
        IncomeStatement::create(['name' => '營業費用']);
        IncomeStatement::create(['name' => '非營業費用']);
        IncomeStatement::create(['name' => '非營業收入']);

        $firstGradeId_1 = FirstGrade::create([
            'code' => '1',
            'has_next_grade' => 1,
            'name' => '資產',
            'acc_company_fk' => '1',
            'acc_income_statement_fk' => '1'
            ])->id;
        $firstGradeId_2 = FirstGrade::create([
            'code' => '2',
            'has_next_grade' => 1,
            'name' => '負債',
            'acc_company_fk' => '1',
            'acc_income_statement_fk' => '1'
            ])->id;
        $firstGradeId_3 = FirstGrade::create([
            'code' => '3',
            'has_next_grade' => 1,
            'name' => '股東權益',
            'acc_company_fk' => '1',
            'acc_income_statement_fk' => '1'
            ])->id;
        $firstGradeId_4 = FirstGrade::create([
            'code' => '4',
            'has_next_grade' => 0,
            'name' => '股東收益',
            'acc_company_fk' => '1',
            'acc_income_statement_fk' => '1'
            ])->id;
        $firstGradeId_5 = FirstGrade::create([
            'code' => '5',
            'has_next_grade' => 1,
            'name' => '股東費用',
            'acc_company_fk' => '1',
            'acc_income_statement_fk' => '1'
            ])->id;

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
        $secondGradeId_5 = DB::table('acc_second_grade')->insertGetId([
            'code' => '51',
            'has_next_grade' => 1,
            'name' => '營業成本',
//            'acc_company_fk' => 1,
            'first_grade_fk' => $firstGradeId_5,
//            'acc_income_statement_fk' => 1
        ]);
        $secondGradeId_6 = DB::table('acc_second_grade')->insertGetId([
            'code' => '52',
            'has_next_grade' => 1,
            'name' => '營業費用',
            //            'acc_company_fk' => 0,
            'first_grade_fk' => $firstGradeId_5,
            //            'acc_income_statement_fk' => 1
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
        $thirdGradeId_7 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1103',
            'has_next_grade' => 1,
            'name' => '外幣',
            'acc_company_fk' => 1,
            'second_grade_fk' => $secondGradeId_1,
            'acc_income_statement_fk' => 1
        ]);
        $thirdGradeId_8 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1104',
            'has_next_grade' => 0,
            'name' => '商品存貨',
//            'acc_company_fk' => 0,
            'second_grade_fk' => $secondGradeId_1,
//            'acc_income_statement_fk' => 1
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
        $thirdGradeId_6 = DB::table('acc_third_grade')->insertGetId([
            'code' => '5201',
            'has_next_grade' => 0,
            'name' => '物流費用',
//            'acc_company_fk' => 1,
            'second_grade_fk' => $secondGradeId_6,
//            'acc_income_statement_fk' => 1
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

        $currencyArray = include 'currency.php';
        foreach ($currencyArray as $key => $currency) {
            DB::table('acc_fourth_grade')->insert([
                'code'                    => '110300' . str_pad($key +1, 2, '0', STR_PAD_LEFT),
                'name'                    => '外幣-'. $currency['name'],
                'acc_company_fk'          => 1,
                'third_grade_fk'          => $thirdGradeId_7,
                'acc_income_statement_fk' => 1,
            ]);
        }

        DB::table('acc_fourth_grade')->insert([
            'code' => '21020001',
            'name' => '應付帳款-其他',
            'acc_company_fk' => 1,
            'third_grade_fk' => $thirdGradeId_5,
            'acc_income_statement_fk' => 1,
            'note_1' => '2014/8/31以前應付帳款轉用'
        ]);
        DB::table('acc_fourth_grade')->insert([
            'code' => '21020002',
            'name' => '應付帳款-茶衣創意',
            'acc_company_fk' => 1,
            'third_grade_fk' => $thirdGradeId_5,
            'acc_income_statement_fk' => 1,
        ]);

        self::insertToAllGradeTable();
    }

    private function insertToAllGradeTable()
    {
        $fourthGradeArray = DB::table('acc_fourth_grade as 4th')
                                ->leftJoin('acc_third_grade as 3rd', '4th.third_grade_fk', '=', '3rd.id')
                                ->leftJoin('acc_second_grade as 2nd', '3rd.second_grade_fk', '=', '2nd.id')
                                ->leftJoin('acc_first_grade as 1st', '2nd.first_grade_fk', '=', '1st.id')
                                ->select([
                                    '1st.id as first_id',
                                    '2nd.id as second_id',
                                    '3rd.id as third_id',
                                    '4th.id as fourth_id',
                                ])
                                ->get();
        $thirdGradeArray = DB::table('acc_third_grade as 3rd')
                                ->leftJoin('acc_second_grade as 2nd', '3rd.second_grade_fk', '=', '2nd.id')
                                ->leftJoin('acc_first_grade as 1st', '2nd.first_grade_fk', '=', '1st.id')
                                ->select([
                                    '1st.id as first_id',
                                    '2nd.id as second_id',
                                    '3rd.id as third_id',
                                ])
                                ->get();
        $secondGradeArray = DB::table('acc_second_grade as 2nd')
                                ->leftJoin('acc_first_grade as 1st', '2nd.first_grade_fk', '=', '1st.id')
                                ->select([
                                    '1st.id as first_id',
                                    '2nd.id as second_id',
                                ])
                                ->get();
        $firstGradeArray = DB::table('acc_first_grade as 1st')
                                ->select([
                                    '1st.id as first_id',
                                ])
                                ->get();

        foreach ($firstGradeArray as $firstGrade) {
            DB::table('acc_all_grades')->insert([
                'acc_first_grade_fk' => $firstGrade->first_id,
            ]);
        }
        foreach ($secondGradeArray as $secondGrade) {
            DB::table('acc_all_grades')->insert([
                'acc_first_grade_fk'  => $secondGrade->first_id,
                'acc_second_grade_fk' => $secondGrade->second_id,
            ]);
        }
        foreach ($thirdGradeArray as $thirdGrade) {
            DB::table('acc_all_grades')->insert([
                'acc_first_grade_fk'  => $thirdGrade->first_id,
                'acc_second_grade_fk' => $thirdGrade->second_id,
                'acc_third_grade_fk'  => $thirdGrade->third_id,
            ]);
        }
        foreach ($fourthGradeArray as $fourthGrade) {
            DB::table('acc_all_grades')->insert([
                'acc_first_grade_fk'  => $fourthGrade->first_id,
                'acc_second_grade_fk' => $fourthGrade->second_id,
                'acc_third_grade_fk'  => $fourthGrade->third_id,
                'acc_fourth_grade_fk' => $fourthGrade->fourth_id,
            ]);
        }
    }
}
