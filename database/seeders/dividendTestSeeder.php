<?php

namespace Database\Seeders;

use App\Enums\Discount\DividendFlag;
use App\Models\Customer;
use App\Models\CustomerDividend;
use Illuminate\Database\Seeder;



class dividendTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = Customer::find(1);
        $token = $user->createToken('My Token')->plainTextToken;

        // 输出令牌
        dd($token);

        // CustomerDividend::getDividendFromErp(1,'aa',1,'M_b2c',1);
        // CustomerDividend::where('flag', 'expired')->delete();

        // foreach (Customer::get() as $value) {
        //     CustomerDividend::checkExpired($value->id, true);
        //     CustomerDividend::checkExpired($value->id);
        // }

        // CustomerDividend::checkExpired(5805,true);
        //
        /*
        $re = CustomerDividend::select(['*'])
            ->selectRaw('CASE category
                        WHEN "cyberbiz" THEN 2
                        WHEN "order" THEN 3
                        WHEN "m_b2e" THEN 1
                        WHEN "m_b2c" THEN 0 END as w')
            ->where('customer_id', 3)
            ->whereIn('flag', [DividendFlag::Active(), DividendFlag::Back()])
            ->orderBy('w', 'ASC')
            ->orderByRaw('CASE WHEN active_edate is null then 1 else 0 end ASC')
            ->orderBy('active_edate', 'ASC')
           
           
        
            ->get()->toArray();

        dd($re);
        */
    }
}
