<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class ReceivedRefund extends Model
{
    use HasFactory;

    protected $table = 'acc_received_refund';
    protected $guarded = [];


    public static function refund_list($id = null, $ro_id = null, $ro_sn = null, $po_id = null, $po_sn = null)
    {
        $query = DB::table('acc_received_refund AS refund')
            ->leftJoin('ord_received_orders AS ro', function($join){
                $join->on('ro.id', '=', 'refund.source_ro_id');
                $join->where([
                    'ro.deleted_at'=>null,
                ]);
            })
            ->leftJoin('pcs_paying_orders AS po', function($join){
                $join->on('po.id', '=', 'refund.append_po_id');
                $join->where([
                    'po.deleted_at'=>null,
                ]);
            })

            ->select(
                'refund.id AS refund_id',
                'refund.title AS refund_title',
                'refund.grade_id AS refund_grade_id',
                'refund.grade_code AS refund_grade_code',
                'refund.grade_name AS refund_grade_name',
                'refund.price AS refund_price',
                'refund.qty AS refund_qty',
                'refund.total_price AS refund_total_price',
                'refund.taxation AS refund_taxation',
                'refund.summary AS refund_summary',
                'refund.note AS refund_note',

                'ro.id AS ro_id',
                'ro.source_type AS ro_source_type',
                'ro.source_id AS ro_source_id',
                'ro.sn AS ro_sn',
                'ro.price AS ro_price',
                'ro.receipt_date AS ro_receipt_date',
                'ro.drawee_id AS ro_target_id',
                'ro.drawee_name AS ro_target_name',
                'ro.drawee_phone AS ro_target_phone',
                'ro.drawee_address AS ro_target_address',

                'po.id AS po_id',
                'po.source_type AS po_source_type',
                'po.source_id AS po_source_id',
                'po.source_sub_id AS po_source_sub_id',
                'po.type AS po_type',
                'po.sn AS po_sn',
                'po.price AS po_price',
                'po.payment_date AS po_payment_date',
                'po.payee_id AS po_target_id',
                'po.payee_name  AS po_target_name',
                'po.payee_phone AS po_target_phone',
                'po.payee_address AS po_target_address',
            );

            if ($id) {
                $query->where(function ($query) use ($id) {
                    if(gettype($id) == 'array') {
                        $query->whereIn('refund.id', $id);
                    } else {
                        $query->where('refund.id', $id);
                    }
                });
            }

            if ($ro_id) {
                $query->where(function ($query) use ($ro_id) {
                    if(gettype($ro_id) == 'array') {
                        $query->whereIn('refund.source_ro_id', $ro_id);
                    } else {
                        $query->where('refund.source_ro_id', $ro_id);
                    }
                });
            }

            if ($ro_sn) {
                $query->where(function ($query) use ($ro_sn) {
                    if(gettype($ro_sn) == 'array') {
                        $query->whereIn('refund.source_ro_sn', $ro_sn);
                    } else {
                        $query->where('refund.source_ro_sn', 'like', "%{$ro_sn}%");
                    }
                });
            }

            if ($po_id) {
                $query->where(function ($query) use ($po_id) {
                    if(gettype($po_id) == 'array') {
                        $query->whereIn('refund.append_po_id', $po_id);
                    } else {
                        $query->where('refund.append_po_id', $po_id);
                    }
                });
            }

            if ($po_sn) {
                $query->where(function ($query) use ($po_sn) {
                    if(gettype($po_sn) == 'array') {
                        $query->whereIn('refund.append_po_sn', $po_sn);
                    } else {
                        $query->where('refund.append_po_sn', 'like', "%{$po_sn}%");
                    }
                });
            }

        return $query->orderBy('refund.created_at', 'DESC');
    }
}
