<?php

namespace Database\Seeders;

use App\Models\SaleChannel;
use Illuminate\Database\Seeder;

use App\Models\Product;
use App\Models\ProductSpec;
use App\Models\ProductSpecItem;
use App\Models\ProductStyle;
use Illuminate\Support\Facades\DB;

class UpdateFromERPDatabase extends Seeder
{
    const SKU = 0;
    const TITLE = 1;
    const SPEC = 2;
    const STYLE_NAME = 3;
    const STOCK_NUMBER = 5;
    /**
     * 把喜多方目前有庫存的商品匯入
     * @return void
     */
    public function run()
    {
        $productsToImport = [
            "P210121017-42","P210121027-42","P210121021-42","P210121022-42","P210121025-42","P210616019-26","P210616019-25","P210616019-512","P210616019-24","P210512002-42","P220427014-42","P210503002-42","P220614008-42","P220614009-42","P220517005-42","P220726001-42","P210506025-42","P210527003-42","P210416007-42","P210416009-42","P210606007-42","P210615004-42","P210615005-42","P210615006-42","P210305049-42","P220325008-42","P220325007-42","P210715019-42","P210323051-42","P210323050-42","P211210006-42","P211210010-42","P210716005-42","P220523013-42","P220531001-42","P220207006-42","P220207009-42","P220110009-42","P220111001-42","P211227012-42","P220207008-42","P211227010-42","P211227011-42","P220720001-42","P220712012-42","P220713002-42","P200929005-42","P220715001-42","P210406014-102","P220310003-42","P220310004-42","P220310006-42","P220310007-42","P220310005-42","P210326024-42","P210416014-365","P210416015-365","P210415067-365","P210415068-365","P210415066-365","P210415069-368","P210604003-42","P210601015-50","P220427024-42","P210722017-42","P210722019-42","P210616005-50","P210616006-50","P220119005-42","P220119006-42","P211222014-42","P210329018-42","P220302010-42","P220302009-42","P220308003-42","P220630009-42","P201116003-42","P220701010-42","P220803003-42","P220510023-42","P220509015-42","P220614001-42","P201110003-84","P220630006-42","P201026015-42","P220510021-42","P220510020-42","P220510022-42","P201110004-42","P201110005-42","P201113011-42","P201110002-42","P201110006-42","P201102001-127","P201102001-128","P201102001-131","P201102001-178","P201204008-50","P210805039-42","P211123002-42","P220517006-42","P210318013-42","P210318014-42","P210318015-42","P210318016-42","P210318017-42","P210729003-42","P220617026-42","P220516006-42","P210503013-42","P210428003-414","P211027006-42","P211027003-42","P211027005-42","P211027001-42","P210106013-42","P210106014-42","P210317027-184","P210317030-184","P220107014-42","P211104011-42","P211222010-42","P211223001-42","P211223002-42","P211222008-42","P211222006-42","P211222002-42","P211223009-42","P211223004-42","P211223005-42","P211223006-42","P211220011-42","P211222001-42","P210818001-42","P210817015-615","P210817015-625","P220803001-42","P210819009-286","P210819014-286","P210222007-42","P210222008-42","P210428015-241","P210428015-327","P220324027-42","P210423035-349","P210423035-326","P210423035-223","P210423035-241","P210423035-412","P210625004-42","P210409046-327","P220613001-818","P220613003-818","P220613002-818","P201016005-42","P201016004-42","P210304028-42","P210305008-42","P210305005-42","P210305007-42","P210308022-42","P210427001-224","P210427001-327","P210427001-328","P210427001-234","P210527002-135","P210223005-42","P220727001-42","P210601010-327","P210601010-335","P210118005-42","P211110001-42","P210715018-42","P201229004-135","P211228001-42","P210627001-42","P210706010-135","P201021001-135","P201203006-184","P220117003-42","P210323054-42","P210223010-42","P201021009-42","P220427001-42","P210304013-42","P210304009-42",
        ];
        $dataToImport = DB::table('product_content')
            ->whereIn('SKUCode', $productsToImport)
            ->leftJoin('product', 'product.ID', '=', 'product_content.PID')
            ->leftJoin('employee', 'product.OP', '=', 'employee.ID')
            ->select([
                'product.PriceSell',
                'product.PriceAGT',
                'product.PriceWeb',
                'product.SalesBonus',
                'product_content.SKUCode',
                'product.OP',
                'product.SaleFlag',
                'product.WebShow',
                'employee.JobNumber',
            ])
            ->get();

        $dataArray = [];
        foreach ($dataToImport as $data) {
            $dataArray[$data->SKUCode] = [
                'PriceSell' => $data->PriceSell,
                'PriceAGT' => $data->PriceAGT,
                //原價
                'PriceWeb' => $data->PriceWeb,
                'SalesBonus' => $data->SalesBonus,
                'OP' => $data->OP,
                'SaleFlag' => $data->SaleFlag,
                'WebShow' => $data->WebShow,
                'JobNumber' => $data->JobNumber,
            ];
        }
        $dataSkusArray = array_keys($dataArray);

        $jsonFileContents = file_get_contents(database_path('seeders/') . 'ittms_bestselection.json');
        $jsonData = json_decode($jsonFileContents, true);
        $arrayProductTitle = array_column($jsonData['data'], self::TITLE);
        $uniqueProductTitles = array_unique($arrayProductTitle);

        foreach ($uniqueProductTitles as $uniqueProductTitle) {
            $productKeys = array_keys($arrayProductTitle, $uniqueProductTitle);
            $productStyleSkus = [];
            $productTitle = '';
            $productStyleNames = [];
            $productStockNumbers = [];
            foreach ($productKeys as $productKey) {
                $productStyleSkus[] = $jsonData['data'][$productKey][self::SKU];
                $productTitle = $jsonData['data'][$productKey][self::TITLE];
                $productStyleNames[] = $jsonData['data'][$productKey][self::STYLE_NAME];
            }

            //員工編號匯入Cyberbiz產品Excel裡面
            $userAccountExist = DB::table('usr_users')
                ->whereNotNull('account')
                ->where('account', '=', $dataArray[$productStyleSkus[0]]['JobNumber'])
                ->exists();
            if ($userAccountExist) {
                $userAccountId = DB::table('usr_users')
                    ->whereNotNull('account')
                    ->where('account', '=', $dataArray[$productStyleSkus[0]]['JobNumber'])
                    ->get()
                    ->first();
                $userId = $userAccountId->id;
            } else {
                $userId = 1;
            }

            $containStyleSkuInIttms = false;
            foreach ($productStyleSkus as $productStyleSku) {
                if (in_array($productStyleSku, $dataSkusArray, true)) {
                    $containStyleSkuInIttms = true;
                }
            }

            $containStyleSkuInERP_Two = DB::table('prd_product_styles')
                ->whereIn('prd_product_styles.sku', $productStyleSkus)
                ->exists();

            if ($containStyleSkuInIttms && !$containStyleSkuInERP_Two) {
                //商品資訊和介紹抓CB的若CB沒有就空白而線上和線下就全否
                $notInCyberbizSkus = [
                    "P210121017-42","P210121027-42","P210121021-42","P210121022-42","P210121025-42","P210512002-42","P210503002-42","P210506025-42","P210527003-42","P210416007-42","P210416009-42","P210305049-42","P210715019-42","P210323051-42","P210323050-42","P210716005-42","P220523013-42","P220531001-42","P220720001-42","P210406014-102","P210326024-42","P210415069-368","P210604003-42","P210601015-50","P210616005-50","P210616006-50","P210329018-42","P201116003-42","P201026015-42","P201110004-42","P201110005-42","P201113011-42","P201110002-42","P201110006-42","P201102001-127","P201102001-128","P201102001-131","P201102001-178","P201204008-50","P210805039-42","P210318013-42","P210318014-42","P210318015-42","P210318016-42","P210318017-42","P210729003-42","P210428003-414","P210106013-42","P210106014-42","P210818001-42","P210819009-286","P210819014-286","P210222007-42","P210222008-42","P220324027-42","P210625004-42","P220613001-818","P220613003-818","P220613002-818","P201016005-42","P201016004-42","P210308022-42","P210527002-135","P210223005-42","P220727001-42","P210118005-42","P211110001-42","P210715018-42","P201229004-135","P211228001-42","P210627001-42","P210706010-135","P201021001-135","P201203006-184","P220117003-42","P210323054-42","P210223010-42","P201021009-42","P220427001-42",
                ];
                $notFoundInCyberbiz = false;
                for ($x = 0; $x < count($productStyleSkus); $x++) {
                    if (($dataArray[$productStyleSkus[$x]]['WebShow'] === '1') &&
                        ($dataArray[$productStyleSkus[$x]]['SaleFlag'] === '1')
                    ) {
                        if (in_array($productStyleSkus[$x], $notInCyberbizSkus)) {
                            $notFoundInCyberbiz = true;
                        }
                    }
                }
                $public = intval($dataArray[$productStyleSkus[0]]['SaleFlag']);
                $online =intval($dataArray[$productStyleSkus[0]]['WebShow']);
                $offline = intval($dataArray[$productStyleSkus[0]]['SaleFlag']);
                if ($notFoundInCyberbiz) {
                    $public = 0;
                    $online = 0;
                    $offline = 0;
                }
                //end of 商品資訊和介紹抓CB的若CB沒有就空白而線上和線下就全否
                print_r('產品：' . $productTitle . PHP_EOL);
                $re = Product::createProduct(
                    $productTitle,
                    $userId,
                    1,
                    'p',
                    null,
                    null,
                    null,
                    null,
                    null,
                    [1],
                    1,
                    0,
                    $public,
                    $online,
                    $offline,
                );
                $productId = $re['id'];
                Product::where('id', $productId)
                    ->update([
                        'spec_locked' => 1,
                        'desc'        => '',
                    ]);

                foreach ($productStyleNames as $key => $productStyleName) {
                    $specId = ProductSpec::where('title', '規格')->get()
                        ->first()->id;
                    Product::setProductSpec($productId, $specId);
                    ProductSpecItem::createItems($productId, $specId, [$productStyleName]);

                    $item_ids = DB::table('prd_spec_items')
                        ->where(['product_id' => $productId])
                        ->whereIn('title', [$productStyleName])
                        ->orderBy('spec_id', 'ASC')
                        ->select('id')
                        ->get()
                        ->toArray();

                    $itemArray = array();
                    foreach ($item_ids as $data) {
                        $itemArray[] = $data->id;
                    }
                    ProductStyle::createStyle($productId, $itemArray);
                    // 更新「款式SKU」成「喜多方SKU」
                    ProductStyle::where([
                        'product_id'    => $productId,
                        'spec_item1_id' => $itemArray[0] ?? null,
                        'spec_item2_id' => $itemArray[1] ?? null,
                        'spec_item3_id' => $itemArray[2] ?? null,
                    ])->update([
                        'sku' => $productStyleSkus[$key],
                    ]);

                    // 銷售通路價格
                    $styleId = ProductStyle::where([
                                'product_id'    => $productId,
                                'spec_item1_id' => $itemArray[0] ?? null,
                                'spec_item2_id' => $itemArray[1] ?? null,
                                'spec_item3_id' => $itemArray[2] ?? null,
                                ])->get()
                                    ->first()
                                    ->id;

                    if ($dataArray[$productStyleSkus[$key]]['PriceWeb'] === 0) {
                        $originPrice = 100000;
                    } else {
                        $originPrice = $dataArray[$productStyleSkus[$key]]['PriceWeb'];
                    }

                    if ($dataArray[$productStyleSkus[$key]]['PriceSell'] === 0) {
                        $price = $originPrice;
                    } else {
                        $price = $dataArray[$productStyleSkus[$key]]['PriceSell'];
                    }

                    if ($dataArray[$productStyleSkus[$key]]['PriceAGT'] === 0) {
                        $dealerPrice = $originPrice;
                    } else {
                        $dealerPrice = $dataArray[$productStyleSkus[$key]]['PriceAGT'];
                    }

                    SaleChannel::changePrice(
                        1,
                        $styleId,
                        $dealerPrice,
                        $price,
                        $originPrice,
                        ($dataArray[$productStyleSkus[$key]]['SalesBonus'] === 0) ? 0 : $dataArray[$productStyleSkus[$key]]['SalesBonus'],
                        0
                    );
                    SaleChannel::addPriceForStyle($styleId);
                }
            }
        }
    }
}
