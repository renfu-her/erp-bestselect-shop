<?php

namespace Database\Seeders;

use App\Models\RptOrganizeReportMonthly;
use App\Models\RptUserReportMonthly;
use App\Models\RptProductReportDaily;
use App\Models\RptProductManagerSaleDaily;
use Illuminate\Database\Seeder;

class reportDevelopmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      //   RptProductManagerSaleDaily::report();
        //

      RptProductReportDaily::report('2022-10-03','date');
        /*
        RptUserReportMonthly::grossProfit();
        RptUserReportMonthly::report('2022-09-01');
        RptOrganizeReportMonthly::report('2022-09-01');
        */
    }
}
