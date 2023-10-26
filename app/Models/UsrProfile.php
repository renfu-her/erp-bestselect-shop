<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UsrProfile extends Model
{
    use HasFactory;
    protected $table = 'usr_profile';
    protected $guarded = [];

    public static function dataList()
    {
        $dateDiff = "DATEDIFF(now(),date_of_job_entry) DIV 30";
        // $dateDiff = 12 * 0.4;
        $re = DB::table('usr_profile as profile')
            ->leftJoin('usr_users as user', 'profile.user_id', '=', 'user.id')
            ->select('profile.*', 'user.name', 'user.account')
            ->selectRaw($dateDiff . ' as month_of_service')
            ->selectRaw('CONCAT(user.company," ",user.department," ",user.group) as department')
            ->selectRaw('CONCAT(FLOOR(' . $dateDiff . ' / 12),"年",MOD(' . $dateDiff . ',12),"個月") as year_of_service')
            ->selectRaw('CASE
                            WHEN  ' . $dateDiff . ' < 6 OR ' . $dateDiff . ' IS NULL THEN 0
                            WHEN  ' . $dateDiff . ' >= 6 AND ' . $dateDiff . ' < 12 THEN 3
                            WHEN ' . $dateDiff . ' >= 12 AND ' . $dateDiff . ' < 12*2 THEN 7
                            WHEN ' . $dateDiff . ' >= 12*2 AND ' . $dateDiff . ' < 12*3 THEN 10
                            WHEN ' . $dateDiff . ' >= 12*3 AND ' . $dateDiff . ' < 12*5 THEN 14
                            WHEN ' . $dateDiff . ' >= 12*5 AND ' . $dateDiff . ' < 12*10 THEN 15
                            WHEN ' . $dateDiff . ' >= 12*10 AND ' . $dateDiff . ' < 12*24 THEN FLOOR((' . $dateDiff . ' - 120) / 12)+1+15
                             ELSE 30  END  as annual_leave');

        // dd($re->get()->toArray()[0]);
        return $re;
    }
}
