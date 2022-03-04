<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IncomeExpenditureSeeder extends Seeder
{
    private const CURRENCY = [
            [
                'name' => 'USD-美金',
                'rate' => 30,
            ],
            [
                'name' => 'JPY-日幣',
                'rate' => 0.29,
            ],
            [
                'name' => 'EUR-歐元',
                'rate' => 35,
            ],
            [
                'name' => 'AUD-澳幣',
                'rate' => 20,
            ],
            [
                'name' => 'CNY-人民幣',
                'rate' => 4.5,
            ],
            [
                'name' => 'THB-泰幣',
                'rate' => 1.1,
            ],
            [
                'name' => 'GBP-英鎊',
                'rate' => 41,
            ],
            [
                'name' => 'KRW-韓幣',
                'rate' => 0.03,
            ],
            [
                'name' => 'CAD-加拿大幣',
                'rate' => 20,
            ],
            [
                'name' => 'HKD-港幣',
                'rate' => 4,
            ],
            [
                'name' => 'NZD-紐西蘭幣',
                'rate' => 20,
            ],
            [
                'name' => 'SGD-新加坡幣',
                'rate' => 22,
            ],
            [
                'name' => 'CHF-法郎',
                'rate' => 32.5,
            ],
        ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //支付方式
        $incomeType_1 = DB::table('acc_income_type')->insertGetId([
            'type' => '現金',
            'grade' => 3,
        ]);
        $incomeType_2 = DB::table('acc_income_type')->insertGetId([
            'type' => '支票',
            'grade' => 4,
        ]);
        $incomeType_3 = DB::table('acc_income_type')->insertGetId([
            'type' => '匯款',
            'grade' => 4,
        ]);
        $incomeType_4 = DB::table('acc_income_type')->insertGetId([
            'type' => '應付帳款',
            'grade' => 4,
        ]);
        $incomeType_5 = DB::table('acc_income_type')->insertGetId([
            'type' => '其它',
            'grade' => 3,
        ]);
        $incomeType_6 = DB::table('acc_income_type')->insertGetId([
            'type' => '外幣',
            'grade' => 4,
        ]);

        //現金
        DB::table('acc_income_expenditure')->insert([
            'acc_income_type_fk' => $incomeType_1,
            'grade_id_fk' => 2,
            'acc_currency_fk' => null,
        ]);
        DB::table('acc_income_expenditure')->insert([
            'acc_income_type_fk' => $incomeType_1,
            'grade_id_fk' => 3,
            'acc_currency_fk' => null,
        ]);

        //支票
        DB::table('acc_income_expenditure')->insert([
            'acc_income_type_fk' => $incomeType_2,
            'grade_id_fk' => 1,
            'acc_currency_fk' => null,
        ]);
        DB::table('acc_income_expenditure')->insert([
            'acc_income_type_fk' => $incomeType_2,
            'grade_id_fk' => 3,
            'acc_currency_fk' => null,
        ]);

        //匯款
        DB::table('acc_income_expenditure')->insert([
            'acc_income_type_fk' => $incomeType_3,
            'grade_id_fk' => 1,
            'acc_currency_fk' => null,
        ]);
        DB::table('acc_income_expenditure')->insert([
            'acc_income_type_fk' => $incomeType_3,
            'grade_id_fk' => 2,
            'acc_currency_fk' => null,
        ]);

        //應付帳款
        DB::table('acc_income_expenditure')->insert([
            'acc_income_type_fk' => $incomeType_4,
            'grade_id_fk' => 2,
            'acc_currency_fk' => null,
        ]);
        DB::table('acc_income_expenditure')->insert([
            'acc_income_type_fk' => $incomeType_4,
            'grade_id_fk' => 4,
            'acc_currency_fk' => null,
        ]);

        //其他
        DB::table('acc_income_expenditure')->insert([
            'acc_income_type_fk' => $incomeType_5,
            'grade_id_fk' => 1,
            'acc_currency_fk' => null,
        ]);
        DB::table('acc_income_expenditure')->insert([
            'acc_income_type_fk' => $incomeType_5,
            'grade_id_fk' => 3,
            'acc_currency_fk' => null,
        ]);

        //外幣
        foreach (self::CURRENCY as $currencyRate) {
            DB::table('acc_currency')->insert($currencyRate);
        }
        for ($index = 1; $index <= 13; $index++) {
            DB::table('acc_income_expenditure')->insert([
                'acc_income_type_fk' => $incomeType_6,
                'grade_id_fk' => ($index % 4) === 0 ? 1 : ($index % 4),
                'acc_currency_fk' => $index,
            ]);
        }
    }
}
