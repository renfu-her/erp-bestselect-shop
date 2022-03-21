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
            'grade_type' => 'App\Models\ThirdGrade'
        ]);
        $incomeType_2 = DB::table('acc_income_type')->insertGetId([
            'type' => '支票',
            'grade' => 4,
            'grade_type' => 'App\Models\FourthGrade'
        ]);
        $incomeType_3 = DB::table('acc_income_type')->insertGetId([
            'type' => '匯款',
            'grade' => 4,
            'grade_type' => 'App\Models\FourthGrade'
        ]);
        $incomeType_4 = DB::table('acc_income_type')->insertGetId([
            'type' => '外幣',
            'grade' => 4,
            'grade_type' => 'App\Models\FourthGrade'
        ]);
        $incomeType_5 = DB::table('acc_income_type')->insertGetId([
            'type' => '應付帳款',
            'grade' => 4,
            'grade_type' => 'App\Models\FourthGrade'
        ]);
        $incomeType_6 = DB::table('acc_income_type')->insertGetId([
            'type' => '其它',
            'grade' => 3,
            'grade_type' => 'App\Models\ThirdGrade'
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
            'acc_income_type_fk' => $incomeType_5,
            'grade_id_fk' => 2,
            'acc_currency_fk' => null,
        ]);
        DB::table('acc_income_expenditure')->insert([
            'acc_income_type_fk' => $incomeType_5,
            'grade_id_fk' => 4,
            'acc_currency_fk' => null,
        ]);

        //其他
        DB::table('acc_income_expenditure')->insert([
            'acc_income_type_fk' => $incomeType_6,
            'grade_id_fk' => 1,
            'acc_currency_fk' => null,
        ]);
        DB::table('acc_income_expenditure')->insert([
            'acc_income_type_fk' => $incomeType_6,
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
                'acc_income_type_fk' => $incomeType_4,
                'grade_id_fk' => $index + 2,
                'acc_currency_fk' => $index,
            ]);
        }

        DB::table('acc_payable')->insert([
            'pay_order_type' => 'App\Models\PayingOrder',
            'payable_type' => 'App\Models\PayableRemit',
            'payable_id' => 1,
            'acc_income_type_fk' => 3,
            'pay_order_id' => 1,
            'tw_price' => 100,
//            'payable_status' => 1,
            'payment_date' => '2022-03-01',
            'accountant_id_fk' => 1,
            'note' => 'aaa',
        ]);

        DB::table('acc_payable_remit')->insert([
            'grade_type' => 'App\Models\FourthGrade',
            'grade_id' => 1,
            'remit_date' => '2022-02-15'
        ]);

        DB::table('acc_payable_cheque')->insert([
            'grade_type' => 'App\Models\FourthGrade',
            'grade_id' => 2,
            'check_num' => "YA12345",
            'maturity_date' => '2022-02-16',
            'cash_cheque_date' => '2022-02-17',
            'cheque_status' => 1
        ]);

        DB::table('acc_payable_default')->insert([
            'product_default_grade_type' => 'App\Models\ThirdGrade',
            'product_default_grade_id' => 4,
            'logistics_default_grade_type' => 'App\Models\ThirdGrade',
            'logistics_default_grade_id' => 8,
        ]);
    }
}
