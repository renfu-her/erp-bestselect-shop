<?php

namespace Database\Seeders;

use App\Enums\Accounting\GradeModelClass;
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
            'first_grade_fk' => $firstGradeId_1,
        ]);
        $secondGradeId_2 = DB::table('acc_second_grade')->insertGetId([
            'code' => '12',
            'has_next_grade' => 1,
            'name' => '固定資產',
            'acc_company_fk' => 1,
            'first_grade_fk' => $firstGradeId_1,
            'acc_income_statement_fk' => 1
        ]);
        $secondGradeId_7 = DB::table('acc_second_grade')->insertGetId([
            'code' => '13',
            'has_next_grade' => 1,
            'name' => '其他資產',
            'first_grade_fk' => $firstGradeId_1,
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
            'second_grade_fk' => $secondGradeId_1,
        ]);
        $thirdGradeId_2 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1102',
            'has_next_grade' => 1,
            'name' => '銀行存款',
            'second_grade_fk' => $secondGradeId_1,
        ]);
        $thirdGradeId_7 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1103',
            'has_next_grade' => 1,
            'name' => '外幣',
            'second_grade_fk' => $secondGradeId_1,
        ]);
        $thirdGradeId_8 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1104',
            'has_next_grade' => 0,
            'name' => '應收票據',
//            'acc_company_fk' => 0,
            'second_grade_fk' => $secondGradeId_1,
//            'acc_income_statement_fk' => 1
        ]);
        $thirdGradeId_9 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1105',
            'has_next_grade' => 1,
            'name' => '應收帳款',
            'second_grade_fk' => $secondGradeId_1,
        ]);
        $thirdGradeId_10 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1106',
            'has_next_grade' => 1,
            'name' => '其他應收款',
            'second_grade_fk' => $secondGradeId_1,
        ]);
        $thirdGradeId_11 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1107',
            'has_next_grade' => 1,
            'name' => '預付款項',
            'second_grade_fk' => $secondGradeId_1,
        ]);
        $thirdGradeId_12 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1108',
            'has_next_grade' => 0,
            'name' => '預付費用',
            'second_grade_fk' => $secondGradeId_1,
        ]);
        $thirdGradeId_13 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1109',
            'has_next_grade' => 1,
            'name' => '信用卡',
            'second_grade_fk' => $secondGradeId_1,
        ]);
        $thirdGradeId_14 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1110',
            'has_next_grade' => 0,
            'name' => '預付團費',
            'second_grade_fk' => $secondGradeId_1,
        ]);
        $thirdGradeId_15 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1111',
            'has_next_grade' => 0,
            'name' => '員工借支',
            'second_grade_fk' => $secondGradeId_1,
        ]);
        $thirdGradeId_16 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1112',
            'has_next_grade' => 0,
            'name' => '暫付款',
            'second_grade_fk' => $secondGradeId_1,
        ]);
        $thirdGradeId_17 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1113',
            'has_next_grade' => 1,
            'name' => '同業往來',
            'second_grade_fk' => $secondGradeId_1,
        ]);
        $thirdGradeId_18 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1114',
            'has_next_grade' => 1,
            'name' => '內部往來',
            'second_grade_fk' => $secondGradeId_1,
        ]);
        $thirdGradeId_19 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1115',
            'has_next_grade' => 0,
            'name' => '消費券',
            'second_grade_fk' => $secondGradeId_1,
        ]);
        $thirdGradeId_20 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1116',
            'has_next_grade' => 1,
            'name' => '預付禮券',
            'second_grade_fk' => $secondGradeId_1,
        ]);
        $thirdGradeId_21 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1117',
            'has_next_grade' => 1,
            'name' => '外幣存款',
            'second_grade_fk' => $secondGradeId_1,
        ]);
        $thirdGradeId_22 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1118',
            'has_next_grade' => 0,
            'name' => '商品存貨',
            'second_grade_fk' => $secondGradeId_1,
        ]);
        $thirdGradeId_23 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1119',
            'has_next_grade' => 0,
            'name' => '商品存貨調整',
            'second_grade_fk' => $secondGradeId_1,
        ]);
        $thirdGradeId_24 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1301',
            'has_next_grade' => 1,
            'name' => '存出保證金',
            'second_grade_fk' => $secondGradeId_7,
        ]);
        $thirdGradeId_25 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1302',
            'has_next_grade' => 0,
            'name' => '開辦費',
            'second_grade_fk' => $secondGradeId_7,
        ]);
        $thirdGradeId_26 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1303',
            'has_next_grade' => 0,
            'name' => '累計攤提-開辦費',
            'second_grade_fk' => $secondGradeId_7,
        ]);
        $thirdGradeId_3 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1201',
            'has_next_grade' => 0,
            'name' => '生財器具',
            'second_grade_fk' => $secondGradeId_2,
        ]);
        $thirdGradeId_27 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1202',
            'has_next_grade' => 0,
            'name' => '累計折舊-生財器具',
            'second_grade_fk' => $secondGradeId_2,
        ]);
        $thirdGradeId_28 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1203',
            'has_next_grade' => 0,
            'name' => '電腦設備',
            'second_grade_fk' => $secondGradeId_2,
        ]);
        $thirdGradeId_29 = DB::table('acc_third_grade')->insertGetId([
            'code' => '1204',
            'has_next_grade' => 0,
            'name' => '累計折舊-電腦設備',
            'second_grade_fk' => $secondGradeId_2,
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
            'note_1' => '喜鴻國際企業 合庫-長春 0844871001158',
            'third_grade_fk' => $thirdGradeId_2,
        ]);
        DB::table('acc_fourth_grade')->insert([
            'code' => '11020002',
            'name' => '銀行存款-合庫長春公司戶B',
            'note_1' => '帳號:0844705375368',
            'third_grade_fk' => $thirdGradeId_2,
        ]);
        DB::table('acc_fourth_grade')->insert([
            'code' => '11020003',
            'name' => '銀行存款-台銀大安公司戶',
            'third_grade_fk' => $thirdGradeId_2,
        ]);

        $currencyArray = include 'currency.php';
        foreach ($currencyArray as $key => $currency) {
            DB::table('acc_fourth_grade')->insert([
                'code'           => '110300'.str_pad($key + 1, 2, '0', STR_PAD_LEFT),
                'name'           => '外幣-'.$currency['name'],
                'third_grade_fk' => $thirdGradeId_7,
            ]);
        }

        $accountReceivedData = [
            '應收帳款-喜鴻旅行社',
            '應收帳款-其他',
            '應收帳款-蝦皮',
            '應收帳款-星夢郵輪',
            '應收帳款-中華郵政',
            '應收帳款-台中榮總',
            '應收帳款-喜鴻餐飲',
            '應收帳款-街口支付',
            '應收帳款-台灣PAY',
            '應收帳款-LINE PAY',
        ];
        foreach ($accountReceivedData as $key => $accountReceivedDatum) {
            DB::table('acc_fourth_grade')->insert([
                'code' => '110500' . str_pad($key + 1, 2, '0', STR_PAD_LEFT),
                'name' => $accountReceivedDatum,
                'third_grade_fk' => $thirdGradeId_9,
            ]);
        }
        DB::table('acc_fourth_grade')->insert([
            'code' => '11060001',
            'name' => '其他應收款-其他',
            'third_grade_fk' => $thirdGradeId_10,
        ]);
        DB::table('acc_fourth_grade')->insert([
            'code' => '11140001',
            'name' => '內部往來-沖帳',
            'third_grade_fk' => $thirdGradeId_18
        ]);
        DB::table('acc_fourth_grade')->insert([
            'code' => '13010001',
            'name' => '存出保證金-其他',
            'third_grade_fk' => $thirdGradeId_24
        ]);
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
                'grade_type' => GradeModelClass::getDescription(1),
                'grade_id' => $firstGrade->first_id,
            ]);
        }
        foreach ($secondGradeArray as $secondGrade) {
            DB::table('acc_all_grades')->insert([
                'grade_type' => GradeModelClass::getDescription(2),
                'grade_id' => $secondGrade->second_id,
            ]);
        }
        foreach ($thirdGradeArray as $thirdGrade) {
            DB::table('acc_all_grades')->insert([
                'grade_type' => GradeModelClass::getDescription(3),
                'grade_id'  => $thirdGrade->third_id,
            ]);
        }
        foreach ($fourthGradeArray as $fourthGrade) {
            DB::table('acc_all_grades')->insert([
                'grade_type' => GradeModelClass::getDescription(4),
                'grade_id' => $fourthGrade->fourth_id,
            ]);
        }
    }
}
