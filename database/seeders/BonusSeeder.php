<?php

namespace Database\Seeders;

use App\Models\CustomerDividend;
use Illuminate\Database\Seeder;

class BonusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
     CustomerDividend::orderDiscount(1, 1, 86);
// CustomerDividend::orderDiscount(1, 1, 40);
      dd(CustomerDividend::getPoints(1)->get()->first()->points);

       exit;

        $re = CustomerDividend::forOrder(1, 1, 100, 0);
        //  dd($re);
        CustomerDividend::activeDividend($re);

        $re = CustomerDividend::forOrder(1, 1, 200, 1);
        CustomerDividend::activeDividend($re, 1);

        $re = CustomerDividend::forOrder(1, 1, 150, 1);
        // CustomerDividend::activeDividend($re, 0);

        $re = CustomerDividend::forOrder(1, 1, 120, 1);
        CustomerDividend::activeDividend($re, 1);

        $re = CustomerDividend::forOrder(1, 1, 12, 1);
        CustomerDividend::activeDividend($re, 0);

        //   CustomerDividend::orderDiscount(1,1,232);

        CustomerDividend::checkExpired(1);
      //  CustomerDividend::orderDiscount(1, 1, 78);
       //  CustomerDividend::orderDiscount(1, 1, 12);

      //  CustomerDividend::orderDiscount(1, 1, 90);
       

        dd(CustomerDividend::getPoints(1)->get()->first()->points);
    }
}
