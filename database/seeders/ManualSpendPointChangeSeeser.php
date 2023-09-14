<?php

namespace Database\Seeders;

use App\Models\ManualDividend;
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

        DB::table('dis_manual_dividend')->update([
            'category' => 'guide',
            'category_title' => '導遊領隊購物金',
        ]);

      //  dd(DB::table('dis_manual_dividend')->get()->toArray());

        //   dd(ManualDividend::get()->toArray());
        $data = DB::table('dis_manual_dividend_log as log')
            ->leftJoin('dis_manual_dividend as md', 'log.manual_dividend_id', '=', 'md.id')
            ->select(['log.*', 'md.note as md_note', 'md.category'])
            ->where('status', '1')->get();
       // dd($data);
        foreach ($data as $d) {
            if (preg_match($pattern, $d->note, $matches)) {
                $outside = $matches[1]; // 括号外的文本
                $inside = $matches[2];
                if ($outside && $inside) {

                    $id = explode(':', $inside);
                    $id = isset($id[1]) ? $id[1] : null;

                    if ($id) {
                        DB::table('usr_cusotmer_dividend')->where('id', $id)->update([
                            'category' => $d->category,
                        ]);
                    }

                }
            }
        }

        echo 'done';

    }
}
