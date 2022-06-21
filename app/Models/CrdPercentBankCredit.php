<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CrdPercentBankCredit extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'crd_percent_bank_credit';
    protected $guarded = [];

    public static function getDetailData($id) {
        $query = DB::table('crd_percent_bank_credit as crd_pbc')
            ->where('crd_pbc.id', '=', $id)
            ->leftJoin('crd_banks', 'crd_banks.id', '=', 'crd_pbc.bank_fk')
            ->leftJoin('crd_credit_cards', 'crd_credit_cards.id', '=', 'crd_pbc.credit_fk')
            ->leftJoinSub(GeneralLedger::getAllGrade(), 'grade', function($join) {
                $join->on('grade.primary_id', 'crd_banks.grade_fk');
            })
            ->select(
            'crd_pbc.id'
                , 'crd_pbc.bank_fk'
                , 'crd_banks.title as bank_title'
                , 'crd_pbc.credit_fk'
                , 'crd_credit_cards.title as credit_title'
                , 'crd_pbc.percent'
                , 'grade.code as grade_code'
                , 'grade.name as grade_name'
            )
        ;
        return $query;
    }

    //請款比例列表
    public static function getList($keyword_bank, $keyword_credit_id) {

        $banks = DB::table('crd_banks')
            ->leftJoinSub(GeneralLedger::getAllGrade(), 'grade', function($join) {
                $join->on('grade.primary_id', 'crd_banks.grade_fk');
            });
        if (isset($keyword_credit_id)) {
            $banks->where('crd_banks.title', 'like', "%{$keyword_bank}%");
        }
        $banks = $banks->get()->toArray();

        $cards = DB::table('crd_credit_cards');
        if (isset($keyword_credit_id)) {
            $cards->where('id', '=', $keyword_credit_id);
        }

        $cards = $cards->get()->toArray();
        $arr = [];
        if (isset($banks) && 0 < count($banks) && isset($cards) && 0 < count($cards)) {
            for($num_bank = 0; $num_bank < count($banks); $num_bank++) {
                for($num_card = 0; $num_card < count($cards); $num_card++) {
                    array_push($arr, ['bank' => (array)$banks[$num_bank], 'credit_card' => (array)$cards[$num_card]]);
                }
            }
        }
        if (0 < count($arr)) {
            $crdPercentBankCredit = CrdPercentBankCredit::all()->toArray();
            for($num_arr = 0; $num_arr < count($arr); $num_arr++) {
                if (isset($crdPercentBankCredit) && 0 < count($crdPercentBankCredit)) {
                    for($num_cpbc = 0; $num_cpbc < count($crdPercentBankCredit); $num_cpbc++) {
                        if ($crdPercentBankCredit[$num_cpbc]['bank_fk'] == $arr[$num_arr]['bank']['id']
                            && $crdPercentBankCredit[$num_cpbc]['credit_fk'] == $arr[$num_arr]['credit_card']['id']
                        ) {
                            $arr[$num_arr]['percent'] = $crdPercentBankCredit[$num_cpbc]['percent'];
                            break;
                        }
                    }
                    if (false == isset($arr[$num_arr]['percent'])) {
                        $arr[$num_arr]['percent'] = 1;
                    }
                } else {
                    $arr[$num_arr]['percent'] = 1;
                }
            }
        }
        return $arr;
    }
}
