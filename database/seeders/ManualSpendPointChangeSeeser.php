<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ManualSpendPointChangeSeeser extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $pattern = '/^(.*?)\((.*?)\)$/';

        $data = DB::table('dis_manual_dividend_log as log')
            ->leftJoin('dis_manual_dividend as md', 'log.manual_dividend_id', '=', 'md.id')
            ->select(['log.*', 'md.note as md_note'])
            ->where('status', '1')->get();
     
        foreach ($data as $d) {
            if (preg_match($pattern, $d->note, $matches)) {
                $outside = $matches[1]; // 括号外的文本
                $inside = $matches[2];
                if ($outside && $inside) {

                    $id = explode(':', $inside);
                    $id = isset($id[1]) ? $id[1] : null;

                    if ($id) {
                        DB::table('usr_cusotmer_dividend')->where('id', $id)->update([
                            'note' => '手動匯入 ' . $d->md_note . " " . $outside,
                        ]);
                    }

                }
            }
        }

        echo 'done';

    }
}
