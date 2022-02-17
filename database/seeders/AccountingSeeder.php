<?php

namespace Database\Seeders;

use App\Models\BalanceSheet;
use App\Models\IncomeStatement;
use Illuminate\Database\Seeder;

class AccountingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BalanceSheet::create(['name' => '資產']);
        BalanceSheet::create(['name' => '負債']);
        BalanceSheet::create(['name' => '股東權益']);
        BalanceSheet::create(['name' => '股東收益']);
        BalanceSheet::create(['name' => '股東費用']);

        IncomeStatement::create(['name' => '營業收入']);
        IncomeStatement::create(['name' => '營業成本']);
        IncomeStatement::create(['name' => '營業費用']);
        IncomeStatement::create(['name' => '非營業費用']);
        IncomeStatement::create(['name' => '非營業收入']);
        //
    }
}
