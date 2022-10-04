<?php

namespace Database\Seeders;

use App\Models\RptUserReportMonthly;
use App\Models\RptOrganizeReportMonthly;
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
        //
         RptUserReportMonthly::report();
         RptOrganizeReportMonthly::report();
    }
}
