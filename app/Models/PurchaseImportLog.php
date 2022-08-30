<?php

namespace App\Models;

use App\Enums\Globals\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PurchaseImportLog extends Model
{
    use HasFactory;
    protected $table = 'pcs_import_log';
    protected $guarded = [];
    public $timestamps = true;
    protected $casts = [
        'expiry_date'  => 'datetime:Y-m-d',
        'created_at'  => 'datetime:Y-m-d',
        'updated_at'  => 'datetime:Y-m-d',
    ];

    public static function createData($purchase, $item = null, $inbound_sn = null, $errMsg, $user) {
        $sn = 'PI' . date("ymd") . str_pad((self::whereDate('created_at', '=', date('Y-m-d'))
                    ->get()
                    ->count()) + 1, 4, '0', STR_PAD_LEFT);
        $status = Status::success()->value;
        if(isset($errMsg)) {
            $status = Status::fail()->value;
        }
        $createData = [];
        $createData['sn'] = $sn;
        $createData['status'] = $status;
        $createData['memo'] = $errMsg ?? null;
        $createData['import_user_id'] = $user->id ?? null;
        $createData['import_user_name'] = $user->name ?? null;
        if (isset($purchase)) {
            $createData['purchase_sn'] = $purchase['purchase_sn'] ?? null;
        }
        if (isset($inbound_sn)) {
            $createData['inbound_sn'] = $inbound_sn ?? null;
        }
        if (isset($item)) {
            $createData['sku'] = $item['sku'] ?? null;
            $createData['title'] = $item['product_title'] ?? null;
            $createData['qty'] = $item['remaining_qty'] ?? null;
            $createData['expiry_date'] = $item['expiry_date'] ?? null;
            $createData['unit_cost'] = $item['unit_cost'] ?? null;
            $createData['price'] = intval($item['remaining_qty'], 0) * intval($item['unit_cost'], 0);
        }

        $purchaseImportLog = PurchaseImportLog::create($createData);
    }

    // 判斷全部SKU是否都存在
    public static function checkSKU($val_pcs) {
        $errMsg = null;
        foreach ($val_pcs['data'] as $key_style => $val_style) {
            $style = DB::table(app(ProductStyle::class)->getTable(). ' as style')
                ->leftJoin(app(Product::class)->getTable(). ' as product', 'product.id', '=', 'style.product_id')
                ->where('style.sku', '=', $val_style['sku'])
                ->select(
                    'style.id as product_style_id'
                    , 'style.sku as sku'
                    , DB::raw('Concat(product.title, "-", style.title) AS product_title'))
                ->first();
            if (false == isset($style)) {
                $errMsg = '無效商品:'. ' '. $val_style['sku']. ' '. '請先於商品管理新增';
                break;
            } else {
                //紀錄存在資料表的名稱、product_style_id 以利建立採購商品、入庫單使用
                $val_pcs['data'][$key_style]['product_title'] = $style->product_title;
                $val_pcs['data'][$key_style]['product_style_id'] = $style->product_style_id;
            }
        }
        if(isset($errMsg)) {
            return ['success' => '0', 'error_msg' => $errMsg];
        } else {
            return ['success' => '1', 'val_pcs' => $val_pcs];
        }
    }

    // 判斷採購人員是否存在
    public static function checkUser($val_pcs) {
        $user = User::where('account', '=', $val_pcs['purchase_user_code'])->first();
        if (false == isset($user)) {
            return ['success' => '0', 'error_msg' => '無此人員工號:'. $val_pcs['purchase_user_code']. ' '. '請先新增員工帳號'];
        }
        return ['success' => '1', 'val_pcs' => $val_pcs, 'data' => $user];
    }

    // 判斷是否有此廠商
    public static function checkSupplier($val_pcs) {
        $supplier = DB::table(app(Supplier::class)->getTable(). ' as supplier')
            ->where(function ($query) use ($val_pcs) {
                $query->where('supplier.vat_no', $val_pcs['supplier_vat_no']);
                $query->orWhere('supplier.name', $val_pcs['supplier_name']);
            })->first();
        if (false == isset($supplier)) {
            return ['success' => '0', 'error_msg' => '無此廠商:'. $val_pcs['supplier_name'][0]. ' '. $val_pcs['supplier_vat_no'][0]. ' '. '請先新增廠商'];
        }

        return ['success' => '1', 'val_pcs' => $val_pcs, 'data' => $supplier];
    }


}
