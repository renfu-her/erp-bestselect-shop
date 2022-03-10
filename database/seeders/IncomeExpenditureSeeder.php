<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IncomeExpenditureSeeder extends Seeder
{
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
        $currencyArray = include 'currency.php';
        foreach ($currencyArray as $currencyRate) {
            DB::table('acc_currency')->insert($currencyRate);
        }
        for ($index = 1; $index <= 13; $index++) {
            DB::table('acc_income_expenditure')->insert([
                'acc_income_type_fk' => $incomeType_6,
                'grade_id_fk' => $index + 2,
                'acc_currency_fk' => $index,
            ]);
        }

        foreach (['付款', '兌現', '押票', '退票', '開票'] as $chequeStatus) {
            DB::table('acc_cheque_status')->insert(['status' => $chequeStatus]);
        }
    }
}
