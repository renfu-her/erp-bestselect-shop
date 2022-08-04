<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\DB;

class TransferVoucher extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'acc_transfer_voucher';
    protected $guarded = [];


    public static function voucher_list(
        $tv_id = null,
        $company_id = null,
        $tv_sn = null,
        $tv_price = null,
        $voucher_date = null,
        $audit_status = 'all',
        $sort = false
    ){
        $sq = '
            SELECT
                acc_all_grades.id,
                CASE
                    WHEN acc_first_grade.code IS NOT NULL THEN acc_first_grade.code
                    WHEN acc_second_grade.code IS NOT NULL THEN acc_second_grade.code
                    WHEN acc_third_grade.code IS NOT NULL THEN acc_third_grade.code
                    WHEN acc_fourth_grade.code IS NOT NULL THEN acc_fourth_grade.code
                    ELSE ""
                END AS code,
                CASE
                    WHEN acc_first_grade.name IS NOT NULL THEN acc_first_grade.name
                    WHEN acc_second_grade.name IS NOT NULL THEN acc_second_grade.name
                    WHEN acc_third_grade.name IS NOT NULL THEN acc_third_grade.name
                    WHEN acc_fourth_grade.name IS NOT NULL THEN acc_fourth_grade.name
                    ELSE ""
                END AS name
            FROM acc_all_grades
            LEFT JOIN acc_first_grade ON acc_all_grades.grade_id = acc_first_grade.id AND acc_all_grades.grade_type = "App\\\Models\\\FirstGrade"
            LEFT JOIN acc_second_grade ON acc_all_grades.grade_id = acc_second_grade.id AND acc_all_grades.grade_type = "App\\\Models\\\SecondGrade"
            LEFT JOIN acc_third_grade ON acc_all_grades.grade_id = acc_third_grade.id AND acc_all_grades.grade_type = "App\\\Models\\\ThirdGrade"
            LEFT JOIN acc_fourth_grade ON acc_all_grades.grade_id = acc_fourth_grade.id AND acc_all_grades.grade_type = "App\\\Models\\\FourthGrade"
        ';

        $sort_query = $sort ? 'debit_credit_code DESC, ' : '';

        $query = DB::table('acc_transfer_voucher AS tv')
            ->leftJoin(DB::raw('(
                SELECT tv_item.voucher_id,
                CONCAT(\'[\', GROUP_CONCAT(\'{
                        "id":"\', tv_item.id, \'",
                        "grade_id":"\', tv_item.grade_id, \'",
                        "grade_code":"\', grade.code, \'",
                        "grade_name":"\', grade.name, \'",
                        "summary":"\', COALESCE(tv_item.summary, ""), \'",
                        "memo":"\', COALESCE(tv_item.memo, ""), \'",
                        "debit_credit_code":"\', tv_item.debit_credit_code, \'",
                        "currency_id":"\', COALESCE(acc_currency.id, ""), \'",
                        "currency_name":"\', COALESCE(acc_currency.name, ""), \'",
                        "rate":"\', COALESCE(tv_item.rate, 1), \'",
                        "currency_price":"\', COALESCE(tv_item.currency_price, ""),\'",
                        "final_price":"\', COALESCE(tv_item.final_price, ""),\'",
                        "department":"\', COALESCE(tv_item.department, ""),\'"
                    }\' ORDER BY ' . $sort_query . ' tv_item.id), \']\') AS items
                FROM acc_transfer_voucher_items AS tv_item
                LEFT JOIN (' . $sq . ') AS grade ON tv_item.grade_id = grade.id
                LEFT JOIN acc_currency ON tv_item.currency_id = acc_currency.id
                GROUP BY tv_item.voucher_id
                ) AS tv_items_table'), function ($join){
                    $join->on('tv_items_table.voucher_id', '=', 'tv.id');
            })
            ->leftJoin('acc_company AS company', function($join){
                $join->on('tv.company_id', '=', 'company.id');
                $join->where([
                    'tv.deleted_at'=>null,
                ]);
            })
            ->leftJoin('usr_users AS creator', function($join){
                $join->on('tv.creator_id', '=', 'creator.id');
                $join->where([
                    'creator.deleted_at'=>null,
                    'tv.deleted_at'=>null,
                ]);
            })
            ->leftJoin('usr_users AS updator', function($join){
                $join->on('tv.updator_id', '=', 'updator.id');
                $join->where([
                    'updator.deleted_at'=>null,
                    'tv.deleted_at'=>null,
                ]);
            })
            ->leftJoin('usr_users AS auditor', function($join){
                $join->on('tv.auditor_id', '=', 'auditor.id');
                $join->where([
                    'auditor.deleted_at'=>null,
                    'tv.deleted_at'=>null,
                ]);
            })
            ->leftJoin('usr_users AS accountant', function($join){
                $join->on('tv.accountant_id', '=', 'accountant.id');
                $join->where([
                    'accountant.deleted_at'=>null,
                    'tv.deleted_at'=>null,
                ]);
            })

            ->whereNull('tv.deleted_at')

            ->where(function ($q) use ($tv_id, $company_id) {
                if($tv_id){
                    if(gettype($tv_id) == 'array') {
                        $q->whereIn('tv.id', $tv_id);
                    } else {
                        $q->where('tv.id', $tv_id);
                    }
                }

                if($company_id){
                    if(gettype($company_id) == 'array') {
                        $q->whereIn('tv.company_id', $company_id);
                    } else {
                        $q->where('tv.company_id', $company_id);
                    }
                }
            })

            ->select(
                'tv.id AS tv_id',
                'tv.sn AS tv_sn',
                'tv.voucher_date AS tv_voucher_date',
                'tv.debit_price AS tv_debit_price',
                'tv.credit_price AS tv_credit_price',
                'tv.audit_status AS tv_audit_status',
                'tv.audit_date AS tv_audit_date',

                'tv_items_table.items AS tv_items',

                'company.id AS company_id',
                'company.company AS company_name',
                'company.address AS company_address',
                'company.phone AS company_phone',
                'company.fax AS company_fax',

                'creator.name AS creator_name',
                'updator.name AS updator_name',
                'auditor.name AS auditor_name',
                'accountant.name AS accountant_name'
            );

        if ($tv_sn) {
            $query->where(function ($query) use ($tv_sn) {
                $query->where('tv.sn', 'LIKE', "%{$tv_sn}%");
            });
        }

        if ($tv_price) {
            if (gettype($tv_price) == 'array' && count($tv_price) == 2) {
                $min_price = $tv_price[0] ?? null;
                $max_price = $tv_price[1] ?? null;
                if($min_price){
                    $query->where('tv.debit_price', '>=', $min_price);
                }
                if($max_price){
                    $query->where('tv.debit_price', '<=', $max_price);
                }
            }
        }

        if ($voucher_date) {
            $s_voucher_date = $voucher_date[0] ? date('Y-m-d', strtotime($voucher_date[0])) : null;
            $e_voucher_date = $voucher_date[1] ? date('Y-m-d', strtotime($voucher_date[1] . ' +1 day')) : null;

            if($s_voucher_date){
                $query->where('tv.voucher_date', '>=', $s_voucher_date);
            }
            if($e_voucher_date){
                $query->where('tv.voucher_date', '<', $e_voucher_date);
            }
        }

        if ($audit_status == 'all') {
            //
        } else if ($audit_status == 0) {
            $query->where('tv.audit_status', '=', 0);
        } else if($audit_status == 1){
            $query->where('tv.audit_status', '=', 1);
        }

        return $query->orderBy('tv.created_at', 'DESC');
    }


    public static function create_transfer_voucher($parm)
    {
        $voucher_date = $parm['voucher_date'];
        $debit_price = $parm['debit_price'] ?? 0;
        $credit_price = $parm['credit_price'] ?? 0;

        $re = self::create([
            'sn'=> 'ZSG' . str_pad((self::get()->count()) + 1, 9, '0', STR_PAD_LEFT),
            'voucher_date' => $voucher_date,
            'debit_price' => $debit_price,
            'credit_price' => $credit_price,
            'company_id' => 1,
            'audit_status' => 0,
            'auditor_id' => null,
            'audit_date' => null,
            'accountant_id' => null,
            'creator_id' => auth('user')->user() ? auth('user')->user()->id : null,
            'updator_id' => auth('user')->user() ? auth('user')->user()->id : null,
        ]);

        return $re;
    }


    public static function delete_voucher($id)
    {
        $target = self::findOrFail($id);

        $target->delete();

        return $target;
    }
}
