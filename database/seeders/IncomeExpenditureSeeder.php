<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Enums\Received\ReceivedMethod;
use App\Enums\Discount\DisCategory;

use App\Models\ReceivedDefault;
use App\Models\PayableDefault;
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


        //付款單科目外幣
        $currencyArray = include 'currency.php';
        foreach ($currencyArray as $key => $currencyRate) {
            DB::table('acc_currency')->insert([
                'name' => $currencyRate['name'],
                'rate' => $currencyRate['rate'],
                //收款單科目外幣
                'received_default_fk' => $key + 1,
            ]);
        }

        //收款單科目外幣
        for ($gradeId = 116; $gradeId <= 128; $gradeId++) {
            ReceivedDefault::insert([
                'name' => ReceivedMethod::ForeignCurrency,
                'default_grade_id' => $gradeId,
            ]);
        }

        // 付款單外幣科目
        for ($i = 116; $i <= 128; $i++) {
            $id = PayableDefault::create([
                'name' => 'foreign_currency',
                'default_grade_id' => $i,
            ])->id;

            DB::table('acc_currency')->where('id', $i - 115)->update([
                'payable_default_fk'=>$id,
            ]);
        }



        if(env('APP_ENV') == 'local' || env('APP_ENV') == 'dev'){
            // 收款單科目
            $received = ReceivedMethod::asArray();
            ksort($received);
            $r_grade_id = [129, 18, 21, 18, 122, 156, 113];
            $i = 0;
            foreach($received as $r_value){
                if($r_value != 'foreign_currency'){
                    ReceivedDefault::create([
                        'name' => $r_value,
                        'default_grade_id' => $r_grade_id[$i],
                    ]);
                    $i++;
                }
            }
            ReceivedDefault::create([
                'name' => 'product',
                'default_grade_id' => 61,
            ]);
            ReceivedDefault::create([
                'name' => 'logistics',
                'default_grade_id' => 74,
            ]);

            $discount_category = DisCategory::asArray();
            ksort($discount_category);
            foreach($discount_category as $dis_value){
                ReceivedDefault::create([
                    'name' => $dis_value,
                    'default_grade_id' => 64,
                ]);
            }


            // 付款單科目
            PayableDefault::create([
                'name' => 'cash',
                'default_grade_id' => 18,
            ]);
            PayableDefault::create([
                'name' => 'cheque',
                'default_grade_id' => 21,
            ]);
            PayableDefault::create([
                'name' => 'remittance',
                'default_grade_id' => 19,
            ]);
            PayableDefault::create([
                'name' => 'accounts_payable',
                'default_grade_id' => 22,
            ]);
            PayableDefault::create([
                'name' => 'other',
                'default_grade_id' => 29,
            ]);
            PayableDefault::create([
                'name' => 'product',
                'default_grade_id' => 35,
            ]);
            PayableDefault::create([
                'name' => 'logistics',
                'default_grade_id' => 100,
            ]);
        }
    }
}
