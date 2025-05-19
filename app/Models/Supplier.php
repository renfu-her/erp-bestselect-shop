<?php

namespace App\Models;

use App\Enums\Supplier\Payment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Supplier extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'prd_suppliers';
    protected $guarded = [];


    /**
     * 建立廠商資料並設定付款方式
     * @param array $data 廠商基本資料
     * @return int 新建立的廠商 ID
     */
    public static function createData(array $data)
    {
        $id = self::create([
            'name' => $data['name'],
            'nickname' => $data['nickname'],
            'vat_no' => $data['vat_no'],
            'postal_code' => $data['postal_code'],
            'contact_address' => $data['contact_address'],
            'contact_person' => $data['contact_person'],
            'job' => $data['job'],
            'contact_tel' => $data['contact_tel'],
            'extension' => $data['extension'],
            'fax' => $data['fax'],
            'mobile_line' => $data['mobile_line'],
            'email' => $data['email'],
            'invoice_address' => $data['invoice_address'],
            'invoice_postal_code' => $data['invoice_postal_code'],
            'invoice_recipient' => $data['invoice_recipient'],
            'invoice_email' => $data['invoice_email'],
            'invoice_phone' => $data['invoice_phone'],
            'invoice_date' => $data['invoice_date'],
            'invoice_date_other' => $data['invoice_date_other'],
            'invoice_ship_fk' => $data['invoice_ship_fk'],
            'invoice_date_fk' => $data['invoice_date_fk'],
            'shipping_address' => $data['shipping_address'],
            'shipping_postal_code' => $data['shipping_postal_code'],
            'shipping_recipient' => $data['shipping_recipient'],
            'shipping_phone' => $data['shipping_phone'],
            'shipping_method_fk' => $data['shipping_method_fk'],
            'pay_date' => $data['pay_date'],
            'account_fk' => $data['account_fk'],
            'account_date' => $data['account_date'],
            'account_date_other' => $data['account_date_other'],
            'request_data' => $data['request_data'],
            'memo' => $data['memo'],
            'def_paytype' => $data['def_paytype'],
        ])->id;

        // 建立付款方式資料
        if (isset($data['paytype'])) {
            foreach ($data['paytype'] as $key => $val) {
                if (Payment::Cheque()->value == $val) {
                    SupplierPayment::createData($id, $val, ['cheque_payable' => $data['cheque_payable'] ?? null]);
                } else if (Payment::Remittance()->value == $val) {
                    SupplierPayment::createData($id, $val, [
                        'bank_cname' => $data['bank_cname'] ?? null,
                        'bank_code' => $data['bank_code'] ?? null,
                        'bank_acount' => $data['bank_acount'] ?? null,
                        'bank_numer' => $data['bank_numer'] ?? null
                    ]);
                } else if (Payment::Other()->value == $val) {
                    SupplierPayment::createData($id, $val, [
                        'other' => $data['other'] ?? null,
                    ]);
                } else {
                    SupplierPayment::createData($id, $val, []);
                }
            }
        }

        return $id;
    }

    /**
     * @param $searchVal
     * @param $duplicate  string dupVatNo:找出重複公司編號   dupSupplierName:找出重複廠商名稱
     * 取得廠商資訊
     * @return \Illuminate\Database\Query\Builder
     */
    public static function getSupplierList($searchVal = null, $duplicate = null)
    {
        $result = DB::table('prd_suppliers as ps')
            ->whereNull('ps.deleted_at')
            ->select(
        'ps.id as id',
                'ps.name as name',
                'ps.nickname as nickname',
                'ps.vat_no as vat_no',
                'ps.contact_person as contact_person',
                'ps.email as email',
                'ps.memo as memo',
            );
        if ($searchVal) {
            $result->where(function ($query) use ($searchVal) {
                $query->Where('ps.name', 'like', "%{$searchVal}%")
                    ->orWhere('ps.nickname', 'like', "%{$searchVal}%")
                    ->orWhere('ps.vat_no', '=', "{$searchVal}");
            });
        }

        if ($duplicate) {
            $duplicateList = [];
            $supplier = DB::table('prd_suppliers')
                        ->whereNull('prd_suppliers.deleted_at');
            if ($duplicate === 'dupVatNo') {
                $data = $supplier->select('vat_no')
                    ->groupBy('prd_suppliers.vat_no')
                    ->havingRaw('COUNT(*) > 1')
                    ->get();
                foreach ($data as $datum) {
                    if ($datum->vat_no !== 'NIL') {
                        $duplicateList[] = $datum->vat_no;
                    }
                }
                $result->whereIn('vat_no', $duplicateList)
                        ->orderBy('vat_no');

            } elseif ($duplicate === 'dupSupplierName') {
                $data = $supplier->select('name')
                    ->groupBy('prd_suppliers.name')
                    ->havingRaw('COUNT(*) > 1')
                    ->get();
                foreach ($data as $datum) {
                    $duplicateList[] = $datum->name;
                }
                $result->whereIn('name', $duplicateList)
                        ->orderBy('name');
            }
        }

//        dd($result->get());
        return $result;
    }

    public static function getProductSupplier($product_id, $just_id = null)
    {
        $re = DB::table('prd_product_supplier as ps')
            ->leftJoin('prd_suppliers as supplier', 'ps.supplier_id', '=', 'supplier.id')
            ->where('ps.product_id', $product_id);

        if (!$just_id) {
            return $re->select('supplier.*')->get()->toArray();
        } else {
            $re = $re->select('supplier.id')->get()->toArray();
            return array_map(function ($n) {
                return $n->id;
            }, $re);
        }

    }

    public static function updateProductSupplier($product_id, $supplier_ids = [])
    {

        DB::table('prd_product_supplier')->where('product_id', $product_id)->delete();
        DB::table('prd_product_supplier')->insert(array_map(function ($n) use ($product_id) {
            return ['product_id' => $product_id, 'supplier_id' => $n];
        }, $supplier_ids));
    }

}
