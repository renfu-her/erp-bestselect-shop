<?php

namespace Database\Seeders;

use App\Models\RptProductManagerReport;
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

        // RptProductReportDaily::report('2022-10-03','date');
        RptProductManagerReport::managerList('season', 2022, ['season' => 3]);

        /*
        RptUserReportMonthly::grossProfit();
        RptUserReportMonthly::report('2022-09-01','month');
        RptOrganizeReportMonthly::report('2022-09-01','month');
        */
    }
}
