<?php

namespace Database\Seeders;

use App\Models\FirstGrade;
use App\Models\IncomeStatement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('acc_company')->insert(['company' => '喜鴻國際有限公司']);

        IncomeStatement::create(['name' => '營業收入']);
        IncomeStatement::create(['name' => '營業成本']);
        IncomeStatement::create(['name' => '營業費用']);
        IncomeStatement::create(['name' => '非營業費用']);
        IncomeStatement::create(['name' => '非營業收入']);

        FirstGrade::create([
            'has_next_grade' => 1,
            'name' => '資產',
            'acc_company_fk' => '1',
            'income_statement_fk' => '1'
            ]);
        FirstGrade::create([
            'has_next_grade' => 1,
            'name' => '負債',
            'acc_company_fk' => '1',
            'income_statement_fk' => '1'
            ]);
        FirstGrade::create([
            'has_next_grade' => 1,
            'name' => '股東權益',
            'acc_company_fk' => '1',
            'income_statement_fk' => '1'
            ]);
        FirstGrade::create([
            'has_next_grade' => 0,
            'name' => '股東收益',
            'acc_company_fk' => '1',
            'income_statement_fk' => '1'
            ]);
        FirstGrade::create([
            'has_next_grade' => 1,
            'name' => '股東費用',
            'acc_company_fk' => '1',
            'income_statement_fk' => '1'
            ]);
        //
    }
}
