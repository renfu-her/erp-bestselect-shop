<?php

namespace App\Models;

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
