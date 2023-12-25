<?php

namespace Database\Seeders;

use App\Models\CustomerDividend;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class dividend_gift_limit_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
     //  dd(CustomerDividend::where('category', 'employee_gift')->get()->toArray());
        
        CustomerDividend::where('category', 'employee_gift')->update([
            'deadline' => 1,
            'active_sdate' => DB::raw('created_at'),
            'active_edate' => Date('Y-12-31 23:59:59'),
        ]);
        
    }
}
