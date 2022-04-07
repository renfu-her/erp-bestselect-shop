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
            ])->id;
        $firstGradeId_3 = FirstGrade::create([
            'code' => '3',
            'has_next_grade' => 1,
            'name' => '股東權益',
            ])->id;
        $firstGradeId_4 = FirstGrade::create([
            'code' => '4',
            'has_next_grade' => 1,
            'name' => '股東收益',
            ])->id;
        $firstGradeId_5 = FirstGrade::create([
            'code' => '5',
            'has_next_grade' => 1,
            'name' => '股東費用',
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
            'first_grade_fk' => $firstGradeId_2,
        ]);
        $secondGradeId_4 = DB::table('acc_second_grade')->insertGetId([
            'code' => '22',
            'has_next_grade' => 0,
            'name' => '長期負債',
            'first_grade_fk' => $firstGradeId_2,
        ]);
        $secondGradeId_8 = DB::table('acc_second_grade')->insertGetId([
            'code' => '23',
            'has_next_grade' => 1,
            'name' => '其他負債',
            'first_grade_fk' => $firstGradeId_2,
        ]);
        $secondGradeId_9 = DB::table('acc_second_grade')->insertGetId([
            'code' => '31',
            'has_next_grade' => 1,
            'name' => '資本',
            'first_grade_fk' => $firstGradeId_3,
        ]);
        $secondGradeId_10 = DB::table('acc_second_grade')->insertGetId([
            'code' => '41',
            'has_next_grade' => 1,
            'name' => '營業收入',
            'first_grade_fk' => $firstGradeId_4,
        ]);
        $secondGradeId_11 = DB::table('acc_second_grade')->insertGetId([
            'code' => '42',
            'has_next_grade' => 1,
            'name' => '營業外收入',
            'first_grade_fk' => $firstGradeId_4,
        ]);
        $secondGradeId_5 = DB::table('acc_second_grade')->insertGetId([
            'code' => '51',
            'has_next_grade' => 1,
            'name' => '營業成本',
            'first_grade_fk' => $firstGradeId_5,
        ]);
        $secondGradeId_6 = DB::table('acc_second_grade')->insertGetId([
            'code' => '52',
            'has_next_grade' => 1,
            'name' => '營業費用',
            'first_grade_fk' => $firstGradeId_5,
        ]);
        $secondGradeId_12 = DB::table('acc_second_grade')->insertGetId([
            'code' => '53',
            'has_next_grade' => 1,
            'name' => '營業外費用',
            'acc_income_statement_fk' => 4,
            'first_grade_fk' => $firstGradeId_5,
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
            'second_grade_fk' => $secondGradeId_3,
        ]);
        $thirdGradeId_5 = DB::table('acc_third_grade')->insertGetId([
            'code' => '2102',
            'has_next_grade' => 1,
            'name' => '應付帳款',
            'second_grade_fk' => $secondGradeId_3,
        ]);
        $thirdGradeId_2_1 = DB::table('acc_third_grade')->insertGetId([
            'code' => '2103',
            'has_next_grade' => 1,
            'name' => '其他應付款',
            'second_grade_fk' => $secondGradeId_3,
        ]);
        $thirdGradeId_2_2 = DB::table('acc_third_grade')->insertGetId([
            'code' => '2104',
            'has_next_grade' => 0,
            'name' => '預收團費',
            'second_grade_fk' => $secondGradeId_3,
        ]);
        $thirdGradeId_2_3 = DB::table('acc_third_grade')->insertGetId([
            'code' => '2105',
            'has_next_grade' => 0,
            'name' => '福利金',
            'second_grade_fk' => $secondGradeId_3,
        ]);
        $thirdGradeId_2_4 = DB::table('acc_third_grade')->insertGetId([
            'code' => '2106',
            'has_next_grade' => 0,
            'name' => '暫收款',
            'second_grade_fk' => $secondGradeId_3,
        ]);
        $thirdGradeId_2_5 = DB::table('acc_third_grade')->insertGetId([
            'code' => '2107',
            'has_next_grade' => 1,
            'name' => '公積金',
            'second_grade_fk' => $secondGradeId_3,
        ]);
        $thirdGradeId_2_6 = DB::table('acc_third_grade')->insertGetId([
            'code' => '2108',
            'has_next_grade' => 0,
            'name' => '短期借款',
            'second_grade_fk' => $secondGradeId_3,
        ]);
        $thirdGradeId_2_7 = DB::table('acc_third_grade')->insertGetId([
            'code' => '2109',
            'has_next_grade' => 1,
            'name' => '預收款項',
            'second_grade_fk' => $secondGradeId_3,
        ]);
        $thirdGradeId_2_8 = DB::table('acc_third_grade')->insertGetId([
            'code' => '2110',
            'has_next_grade' => 0,
            'name' => '福利互助金	',
            'second_grade_fk' => $secondGradeId_3,
        ]);
        $thirdGradeId_2_9 = DB::table('acc_third_grade')->insertGetId([
            'code' => '2111',
            'has_next_grade' => 1,
            'name' => '其他暫收款	',
            'second_grade_fk' => $secondGradeId_3,
        ]);
        $thirdGradeId_2_10 = DB::table('acc_third_grade')->insertGetId([
            'code' => '2301',
            'has_next_grade' => 0,
            'name' => '存入保證金',
            'second_grade_fk' => $secondGradeId_8,
        ]);
        $thirdGradeId_2_11 = DB::table('acc_third_grade')->insertGetId([
            'code' => '2302',
            'has_next_grade' => 0,
            'name' => '高爾夫球隊基金',
            'second_grade_fk' => $secondGradeId_8,
        ]);

        $thirdGradeId_3_1 = DB::table('acc_third_grade')->insertGetId([
            'code' => '3101',
            'has_next_grade' => 0,
            'name' => '股本',
            'second_grade_fk' => $secondGradeId_9,
        ]);
        $thirdGradeId_3_2 = DB::table('acc_third_grade')->insertGetId([
            'code' => '3102',
            'has_next_grade' => 0,
            'name' => '本期損益',
            'second_grade_fk' => $secondGradeId_9,
        ]);
        $thirdGradeId_3_3 = DB::table('acc_third_grade')->insertGetId([
            'code' => '3103',
            'has_next_grade' => 0,
            'name' => '累計盈虧',
            'second_grade_fk' => $secondGradeId_9,
        ]);
        $thirdGradeId_3_4 = DB::table('acc_third_grade')->insertGetId([
            'code' => '3104',
            'has_next_grade' => 0,
            'name' => '前期損益調整',
            'second_grade_fk' => $secondGradeId_9,
        ]);

        $fourthGradeData = [
            '銷貨收入',
            '銷貨退回',
            '紅利折扣',
            '優惠券折扣',
            '任選折扣',
            '銷貨折扣-蝦皮-蝦幣折抵',
            '全館活動折扣',
        ];
        foreach ($fourthGradeData as $key => $fourthGradeDatum) {
            DB::table('acc_third_grade')->insertGetId([
                'code'           => '41' . str_pad($key + 1, 2, '0', STR_PAD_LEFT),
                'has_next_grade'       => 0,
                'name'                 => $fourthGradeDatum,
                'acc_income_statement_fk' => 1,
                'second_grade_fk'      => $secondGradeId_10,
            ]);
        }

        $fourthGradeData_1 = [
            '利息收入',
            '兌換盈益',
            '其他收入',
            '租金收入',
            '贊助回饋金收入',
            '佣金收入',
        ];
        foreach ($fourthGradeData_1 as $key => $fourthGradeDatum_1) {
            DB::table('acc_third_grade')->insertGetId([
                'code'           => '42' . str_pad($key + 1, 2, '0', STR_PAD_LEFT),
                'has_next_grade'       => 0,
                'name'                 => $fourthGradeDatum_1,
                'acc_income_statement_fk' => 5,
                'second_grade_fk'      => $secondGradeId_11,
            ]);
        }
        $thirdGradeId_4_1 = DB::table('acc_third_grade')->insertGetId([
            'code'           => '4207',
            'has_next_grade'       => 1,
            'name'                 => '物流收入',
            'acc_income_statement_fk' => 5,
            'second_grade_fk'      => $secondGradeId_11,
        ]);

        $thirdGradeData = [
            '薪資支出',
            '租金支出',
            '文具用品',
            '差旅費',
            '快遞費',
            '郵電費',
            '修繕費',
            '廣告費',
            '水費',
            '電費',
        ];
        foreach ($thirdGradeData as $key => $thirdGradeDatum) {
            DB::table('acc_third_grade')->insertGetId([
                'code'            => '52' . str_pad($key + 1, 2, '0', STR_PAD_LEFT),
                'has_next_grade'  => 0,
                'name'            => $thirdGradeDatum,
                'acc_income_statement_fk' => 3,
                'second_grade_fk' => $secondGradeId_6,
            ]);
        }
        DB::table('acc_third_grade')->insertGetId([
            'code'            => '5211',
            'has_next_grade'  => 0,
            'name'            => '瓦斯費',
            'acc_income_statement_fk' => 4,
            'second_grade_fk' => $secondGradeId_6,
        ]);

        $thirdGradeData_1 = [
            '電話費',
            '健保費',
            '勞保費',
            '交際費',
            '印刷費',
            '稅捐',
            '折舊',
            '雜項支出',
        ];
        foreach ($thirdGradeData_1 as $key => $thirdGradeDatum) {
            DB::table('acc_third_grade')->insertGetId([
                'code'            => '52' . str_pad($key + 12, 2, '0', STR_PAD_LEFT),
                'has_next_grade'  => 0,
                'name'            => $thirdGradeDatum,
                'acc_income_statement_fk' => 3,
                'second_grade_fk' => $secondGradeId_6,
            ]);
        }

        DB::table('acc_third_grade')->insertGetId([
            'code'            => '5226',
            'has_next_grade'  => 0,
            'name'            => '刷卡手續費',
            'acc_income_statement_fk' => 4,
            'second_grade_fk' => $secondGradeId_6,
        ]);
        DB::table('acc_third_grade')->insertGetId([
            'code'            => '5227',
            'has_next_grade'  => 0,
            'name'            => '電腦用品',
            'acc_income_statement_fk' => 3,
            'second_grade_fk' => $secondGradeId_6,
        ]);
        DB::table('acc_third_grade')->insertGetId([
            'code'            => '5228',
            'has_next_grade'  => 0,
            'name'            => '保險費',
            'acc_income_statement_fk' => 4,
            'second_grade_fk' => $secondGradeId_6,
        ]);
        DB::table('acc_third_grade')->insertGetId([
            'code'            => '5229',
            'has_next_grade'  => 0,
            'name'            => '紅利點數',
            'acc_income_statement_fk' => 4,
            'second_grade_fk' => $secondGradeId_6,
        ]);
        // TODO delete 5230

        $thirdGradeData_53 = [
            '兌換損失',
            '其他損失',
            '職工福利',
            '捐贈',
            '書報費',
            '交通費',
            '訓練費',
            '佣金支出',
            '利息支出',
        ];
        foreach ($thirdGradeData_53 as $key => $thirdGradeDatum) {
            DB::table('acc_third_grade')->insertGetId([
                'code'            => '53' . str_pad($key + 1, 2, '0', STR_PAD_LEFT),
                'has_next_grade'  => 0,
                'name'            => $thirdGradeDatum,
                'acc_income_statement_fk' => 4,
                'second_grade_fk' => $secondGradeId_12,
            ]);
        }

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
            'third_grade_fk' => $thirdGradeId_5,
            'note_1' => '2014/8/31以前應付帳款轉用',
            'note_2' => '2014/9/1之後新增用'
        ]);
        DB::table('acc_fourth_grade')->insert([
            'code' => '21020002',
            'name' => '應付帳款-茶衣創意',
            'third_grade_fk' => $thirdGradeId_5,
        ]);

        $logisticData = [
            '喬元手創食品(果木小薰)',
            '桔豐科技',
            '公務車',
            '和生御品',
            '美麗心靈',
            '東雅小廚館',
            '千櫻國際',
            '尊榮生活電商',
            '廣泰興',
            '宏光開發',
        ];
        foreach ($logisticData as $key => $logisticDatum) {
            DB::table('acc_fourth_grade')->insert([
                'code'                    => '420700' . str_pad($key + 1, 2, '0', STR_PAD_LEFT),
                'name'                    => '物流收入-' . $logisticDatum,
                'acc_income_statement_fk' => 5,
                'third_grade_fk'          => $thirdGradeId_4_1,
            ]);
        }

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
