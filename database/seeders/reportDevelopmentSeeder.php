<?php

namespace Database\Seeders;

use App\Models\RptProductManagerReport;
use Illuminate\Database\Seeder;
use App\Models\RptProductReportDaily;

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

         RptProductReportDaily::report('2022-10-01');
      //  RptProductManagerReport::managerList('season', 2022, ['season' => 3]);

        /*
        RptUserReportMonthly::grossProfit();
        RptUserReportMonthly::report('2022-09-01','month');
        RptOrganizeReportMonthly::report('2022-09-01','month');
        */
    }
}
