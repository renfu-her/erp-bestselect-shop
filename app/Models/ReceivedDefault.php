<?php

namespace App\Models;

use App\Enums\Received\ReceivedMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ReceivedDefault extends Model
{
    use HasFactory;

    public static function updateCurrency(array $currencyData)
    {
        foreach ($currencyData['rate'] as $currencyId => $rate) {
            DB::table('acc_currency')
                ->where('acc_currency.id', '=', $currencyId)
                ->update(['rate' => $rate]);
        }

        DB::table('acc_received_default')
            ->where('name', '=', ReceivedMethod::ForeignCurrency)
            ->delete();

        if (isset($currencyData['grade_id_fk'])) {
            foreach ($currencyData['grade_id_fk'] as $currencyId => $grade_id_fk) {
                $defaultPrimaryId = DB::table('acc_received_default')
                    ->insertGetId([
                        'name' => ReceivedMethod::ForeignCurrency,
                        'default_grade_id' => $grade_id_fk
                    ]);
                DB::table('acc_currency')
                    ->where('id', '=', $currencyId)
                    ->update(['received_default_fk' => $defaultPrimaryId]);
            }
        }
    }

    public static function updateReceivedDefault(array $request)
    {
        DB::table('acc_received_default')
            ->whereIn('name', ReceivedMethod::asArray())
            ->where('name', '<>', ReceivedMethod::ForeignCurrency)
            ->delete();

        foreach ($request as $receivedMethod => $gradeIds) {
            //外匯付款方式使用其它function處理
            if (in_array($receivedMethod, ReceivedMethod::asArray()) &&
                $receivedMethod !== ReceivedMethod::ForeignCurrency) {
                foreach ($gradeIds['default_grade_id'] as $gradeId) {
                    DB::table('acc_received_default')
                        ->insert([
                            'name'             => $receivedMethod,
                            'default_grade_id' => $gradeId
                        ]);
                }
            }
        }
    }
}
