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
         RptUserReportMonthly::report('2022-09-01');
         RptOrganizeReportMonthly::report('2022-09-01');
    }
}
