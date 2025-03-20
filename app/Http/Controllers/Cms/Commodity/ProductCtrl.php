<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\eTicket\ETicketVendor;
use App\Enums\Globals\AppEnvClass;
use App\Exports\Product\ProductInforExport;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Depot;
use App\Models\Product;
use App\Models\ProductImg;
use App\Models\ProductSpec;
use App\Models\ProductSpecItem;
use App\Models\ProductSpecList;
use App\Models\ProductStyle;
use App\Models\ProductStyleCombo;
use App\Models\SaleChannel;
use App\Models\ShipmentCategory;
use App\Models\Supplier;
use App\Models\TikType;
use App\Models\User;
use App\Services\ETickets\AutoEticketPurchaseDeliveryServices;
use Illuminate\Http\Request;
// use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Maatwebsite\Excel\Facades\Excel;

class ProductCtrl extends Controller
{

    public $currentPage = 1;

    public function __construct(Request $request)
    {
        $query = $request->query();
        $this->currentPage = Arr::get($query, 'page', 1);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $imgFile = Image::blur();

        // dd(Product::productList(null, null, ['collection' => 1, 'price' => 1,'img'=>1])->get()->toArray());

        $query = $request->query();
        $productTypes = [['all', '不限'], ['p', '一般商品'], ['c', '組合包商品']];
        $onlineTypes = [['all', '不限'], ['online', '線上 (對外網站)'], ['offline', '線下 (ERP)']];
        $consumes = [['all', '不限'], ['1', '耗材'], ['0', '商品']];
        $publics = [['all', '不限'], ['1', '公開'], ['0', '不公開']];
        $hasDelivery = [['all', '不限'], ['1', '有'], ['0', '無']];
        $hasSpecList = [['all', '不限'], ['1', '有'], ['0', '無']];
        $isLiquor = [['all', '不限'], ['1', '有']];

        $page = getPageCount(Arr::get($query, 'data_per_page'));
        $cond = [];
        $cond['keyword'] = Arr::get($query, 'keyword');
        $cond['user'] = Arr::get($query, 'user', []);

        if (count($cond['user']) == 0) {
            $condUser = true;
        } else {
            $condUser = $cond['user'];
        }

        $cond['product_type'] = Arr::get($query, 'product_type', 'all');
        $cond['consume'] = Arr::get($query, 'consume', 'all');
        $cond['public'] = Arr::get($query, 'public', '1');
        $cond['online'] = Arr::get($query, 'online', 'all');
        $cond['hasDelivery'] = Arr::get($query, 'hasDelivery', 'all');
        $cond['hasSpecList'] = Arr::get($query, 'hasSpecList', 'all');
        $cond['search_supplier'] = Arr::get($query, 'search_supplier', 'all');
        $cond['is_liquor'] = Arr::get($query, 'is_liquor', 'all');

        $products = Product::productList($cond['keyword'], null, [
            'user' => $condUser,
            'product_type' => $cond['product_type'],
            'consume' => $cond['consume'] == 'all' ? null : $cond['consume'],
            'public' => $cond['public'] == 'all' ? null : $cond['public'],
            'hasDelivery' => $cond['hasDelivery'],
            'hasSpecList' => $cond['hasSpecList'] == 'all' ? null : $cond['hasSpecList'],
            'online' => $cond['online'],
            'search_supplier' => $cond['search_supplier'] == 'all' ? null : $cond['search_supplier'],
            'is_liquor' => $cond['is_liquor'],
        ])
            ->paginate($page)->appends($query);

        return view('cms.commodity.product.list', [
            'dataList' => $products,
            'productTypes' => $productTypes,
            'onlineTypes' => $onlineTypes,
            'users' => User::get(),
            'data_per_page' => $page,
            'consumes' => $consumes,
            'publics' => $publics,
            'isLiquor' => $isLiquor,
            'hasDelivery' => $hasDelivery,
            'hasSpecList' => $hasSpecList,
            'suppliers' => Supplier::select('name', 'id', 'vat_no')->get()->toArray(),
            'cond' => $cond,
            'page' => $this->currentPage]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        return view('cms.commodity.product.basic_info', [
            'method' => 'create',
            'formAction' => Route('cms.product.create', ['page' => $this->currentPage]),
            'users' => User::get(),
            'suppliers' => Supplier::get(),
            'categorys' => Category::get(),
            'tikTypes' => TikType::where('is_active', 1)->get(),
            'current_user' => $request->user()->id,
            'images' => [],
            'page' => $this->currentPage,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'files.*' => 'max:5000|mimes:jpg,jpeg,png,bmp',
            'title' => 'required',
            'has_tax' => 'required',
            'active_sdate' => 'date|nullable',
            'active_edate' => 'date|nullable',
            'user_id' => 'required',
            'category_id' => 'required',
            'supplier' => 'required|array',
            'type' => 'required|in:c,p',
            // 'url'=>'unique:App\Models\Product'
            'tik_type_id' => 'required',
        ]);

        // $path = $request->file('file')->store('excel');

        $d = $request->all();
        $validationResult = $this->validateYoubonEticket($d['tik_type_id'], $d);
        if ($validationResult['success'] != 1 ) {
            wToast($validationResult['message'], ['type' => 'danger']);
            return redirect()->back();
        }
        $re = Product::createProductWithTicket($d['title'],
            $d['user_id'],
            $d['category_id'],
            $d['type'],
            $d['feature'],
            '',
            $d['slogan'],
            $d['active_sdate'],
            $d['active_edate'],
            $d['supplier'],
            $d['has_tax'],
            isset($d['consume']) ? $d['consume'] : '0',
            isset($d['public']) ? $d['public'] : '0',
            isset($d['online']) ? $d['online'] : '0',
            isset($d['offline']) ? $d['offline'] : '0',
            $d['purchase_note'],
            $d['meta'],
            $d['tik_type_id']);
        // 判斷若為電子票券，則自動建立運送方式
        $tikType = TikType::where('code', ETicketVendor::YOUBON_CODE)->first();
        if ($tikType && $d['tik_type_id'] == $tikType->id) {
            Product::updateETicketProductShipment($re['id']);
        }

        if ($request->hasfile('files')) {
            foreach (array_reverse($request->file('files')) as $file) {

                $img = self::imgResize($file->path());
                $filename = self::imgFilename($re['id'], $file->hashName());

                if (App::environment(AppEnvClass::Release)) {
                    if (Storage::disk('ftp')->put($filename, $img)) {
                        $imgData[] = $filename;
                    }
                } else {
                    if (Storage::disk('local')->put($filename, $img)) {
                        $imgData[] = $filename;
                    }
                }
            }

            ProductImg::createImgs($re['id'], $imgData);
        }

        if ($d['type'] == 'p') {
            $url = 'cms.product.edit-style';
        } else {
            $url = 'cms.product.edit-combo';
        }

        wToast('新增完畢');
        return redirect(route($url, ['id' => $re['id']]));
    }

    /**
     * 檢查星全安電子票券相關規則
     *
     * @param int $tikTypeId 票券類型 ID
     * @param array $data 送出的資料
     * @return array ['success' => bool, 'message' => string] 檢查結果
     */
    private function validateYoubonEticket($tikTypeId, $data)
    {
        // 檢查是否為星全安電子票券
        $tikType = TikType::where('code', ETicketVendor::YOUBON_CODE)->first();

        // 如果不是星全安電子票券，直接通過檢查
        if (!$tikType || $tikTypeId != $tikType->id) {
            return ['success' => 1, 'message' => ''];
        }

        // 檢查商品類型不可以為組合包
        if ($data['type'] == 'c') {
            return [
                'success' => 0,
                'message' => '若選擇商品類型是星全安電子票券，則商品類型不可以為組合包'
            ];
        }

        // 檢查廠商只能選擇星全安旅行社有限公司
        $autoPurchaseDeliveryServices = new AutoEticketPurchaseDeliveryServices();
        $youbonSupplier = $autoPurchaseDeliveryServices->getYoubonSupplier();

        if (!in_array($youbonSupplier->id, $data['supplier']) || 1 != count($data['supplier'])) {
            return [
                'success' => 0,
                'message' => '若選擇商品類型是星全安電子票券，則廠商必須為星全安旅行社有限公司'
            ];
        }

        // 所有檢查都通過
        return ['success' => 1, 'message' => ''];
    }

    /**
     * 取得商品資料共用
     * @return object
     */

    private static function product_data($id, $full = false)
    {
        $data = Product::where('id', $id)->get()->first();
        if (!$full) {
            $data->select('id', 'title', 'type');
        }
        if (!$data) {
            return abort(404);
        }
        return $data;
    }

    /**
     * 編輯 - 商品資訊
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $product = self::product_data($id, true);

        $product->active_sdate = $product->active_sdate ? date('Y-m-d', strtotime($product->active_sdate)) : null;
        $product->active_edate = $product->active_edate ? date('Y-m-d', strtotime($product->active_edate)) : null;

        $current_supplier = Supplier::getProductSupplier($id, true);

        return view('cms.commodity.product.basic_info', [
            'method' => 'edit',
            'formAction' => Route('cms.product.edit', ['id' => $id, 'page' => $this->currentPage]),
            'users' => User::where('department','商品部')->get(),
            'product' => $product,
            'suppliers' => Supplier::get(),
            'current_supplier' => $current_supplier,
            'categorys' => Category::get(),
            'images' => ProductImg::where('product_id', $id)->get(),
            'tikTypes' => TikType::where('is_active', 1)->get(),
            'breadcrumb_data' => $product,
            'page' => $this->currentPage,
        ]);
    }

    /**
     * 編輯 - 商品資訊 儲存
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'files.*' => 'max:5000|mimes:jpg,jpeg,png,bmp',
            //  'url' => ["unique:App\Models\Product,url,$id,id", 'nullable'],
            'title' => 'required',
            'has_tax' => 'required',
            'active_sdate' => 'date|nullable',
            'active_edate' => 'date|nullable',
            'user_id' => 'required',
            'category_id' => 'required',
            'supplier' => 'required|array',

        ]);

        $d = $request->all();

        $validationResult = $this->validateYoubonEticket($d['tik_type_id'], $d);
        if ($validationResult['success'] != 1) {
            wToast($validationResult['message'], ['type' => 'danger']);
            return redirect()->back();
        }

        Product::updateProduct($id,
            $d['title'],
            $d['user_id'],
            $d['category_id'],
            $d['feature'],
            '',
            $d['slogan'],
            $d['active_sdate'],
            $d['active_edate'],
            $d['supplier'],
            $d['has_tax'],
            isset($d['consume']) ? $d['consume'] : '0',
            isset($d['public']) ? $d['public'] : '0',
            isset($d['online']) ? $d['online'] : '0',
            isset($d['offline']) ? $d['offline'] : '0',
            $d['purchase_note'],
            $d['meta'],
            $d['tik_type_id']
        );

        if ($request->hasfile('files')) {
            foreach (array_reverse($request->file('files')) as $file) {

                $img = self::imgResize($file->path());
                $filename = self::imgFilename($id, $file->hashName());

                if (App::environment(AppEnvClass::Release)) {
                    if (Storage::disk('ftp')->put($filename, $img)) {
                        $imgData[] = $filename;
                    }
                    // $imgData[] = $file->store('product_imgs/' . $id, 'ftp');
                } else {
                    if (Storage::disk('local')->put($filename, $img)) {
                        $imgData[] = $filename;
                    }
                    // $imgData[] = $file->store('product_imgs/' . $id);
                }
            }
            ProductImg::createImgs($id, $imgData);
        }

        if (isset($d['del_image']) && $d['del_image']) {
            ProductImg::delImgs(explode(',', $d['del_image']));
        }

        wToast('儲存完畢');
        $page = Arr::get($request->query(), 'page', 1);
        return redirect(Route('cms.product.index', ['page' => $page]));

        //
    }

    /**
     * 編輯 - 規格款式
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editStyle($id)
    {

        $product = self::product_data($id);
        $specList = ProductSpec::specList($id);
        $styles = ProductStyle::styleList($id)->get()->toArray();
        // dd($styles);
        $init_styles = [];
        if (count($styles) == 0) {
            $init_styles = ProductStyle::createInitStyles($id);
        }

        $salechannel = SaleChannel::where('is_master', 1)->get()->first();

        $product = Product::where('id', $id)->get()->first();
        $tiktype = TikType::where('code', '!=', 'general')->get();
        // 判斷 $product.tikt_type_id 是否在 $tiktype->id 裡，有的話設定 isTik = ture，否則 false
        $product->isTicket = false;
        foreach ($tiktype as $tik) {
            if ($product->tik_type_id == $tik->id) {
                $product->isTicket = true;
            }
        }

        // dd($product, $styles, $specList);
        return view('cms.commodity.product.styles', [
            'data' => $product,
            'specList' => $specList,
            'styles' => $styles,
            'initStyles' => $init_styles,
            'product' => $product,
            'breadcrumb_data' => $product,
            'salechannel' => $salechannel,
        ]);
    }

    public function storeStyle(Request $request, $id)
    {
        /*

        $request->validate([
        'nsk_price' => 'required|array',
        'nsk_price.*' => 'required|numeric',
        'nsk_dealer_price' => 'required|array',
        'nsk_dealer_price.*' => 'required|numeric',
        'nsk_origin_price' => 'required|array',
        'nsk_origin_price.*' => 'required|numeric',
        'nsk_bonus' => 'required|array',
        'nsk_bonus.*' => 'required|numeric',
        'sk_price' => 'required|array',
        'sk_price.*' => 'required|numeric',
        'sk_dealer_price' => 'required|array',
        'sk_dealer_price.*' => 'required|numeric',
        'sk_origin_price' => 'required|array',
        'sk_origin_price.*' => 'required|numeric',
        'sk_bonus' => 'required|array',
        'sk_bonus.*' => 'required|numeric',
        'n_price' => 'required|array',
        'n_price.*' => 'required|numeric',
        'n_dealer_price' => 'required|array',
        'n_dealer_price.*' => 'required|numeric',
        'n_origin_price' => 'required|array',
        'n_origin_price.*' => 'required|numeric',
        'n_bonus' => 'required|array',
        'n_bonus.*' => 'required|numeric',

        ]);
         */

        $specCount = DB::table('prd_product_spec')->where('product_id', $id)->count();
        $sale_id = (SaleChannel::where('code', '01')->select('id')->get()->first())->id;

        $d = $request->all();
        if (isset($d['nsk_style_id'])) {
            foreach ($d['nsk_style_id'] as $key => $value) {
                $updateData = [];
                $itemIds = [];
                for ($i = 1; $i <= $specCount; $i++) {
                    if (isset($d["nsk_spec$i"][$key])) {
                        $updateData["estimated_cost"] = $d['nsk_estimated_cost'][$key];
                        $updateData["ticket_number"] = $d['nsk_ticket_number'][$key] ?? null;
                        $itemIds[] = $d['nsk_spec' . $i][$key];
                    }
                }
                // $updateData['sold_out_event'] = $d['nsk_sold_out_event'][$key];

                //  ProductStyle::where('id', $value)->whereNull('sku')->update($updateData);
                ProductStyle::updateStyle($value, $id, $itemIds, $updateData);
                SaleChannel::changePrice($sale_id, $value, $d['nsk_dealer_price'][$key], $d['nsk_price'][$key], $d['nsk_origin_price'][$key], $d['nsk_bonus'][$key], $d['nsk_dividend'][$key]);
            }
        }

        if (isset($d['sk_style_id'])) {
            foreach ($d['sk_style_id'] as $key => $value) {
                $updateData = [];
                $updateData['estimated_cost'] = $d['sk_estimated_cost'][$key];
                $updateData['ticket_number'] = $d['sk_ticket_number'][$key] ?? null;
                ProductStyle::where('id', $value)->whereNotNull('sku')->update($updateData);
                SaleChannel::changePrice($sale_id, $value, $d['sk_dealer_price'][$key], $d['sk_price'][$key], $d['sk_origin_price'][$key], $d['sk_bonus'][$key], $d['sk_dividend'][$key]);

            }
        }

        if (isset($d['active_id'])) {
            ProductStyle::activeStyle($id, $d['active_id']);
        }

        if (isset($d['n_sold_out_event'])) {
            $newItemCount = count($d['n_sold_out_event']);
            for ($i = 0; $i < $newItemCount; $i++) {
                $updateData = [];
                for ($j = 1; $j <= $specCount; $j++) {
                    if (isset($d["n_spec$j"][$i])) {
                        $updateData["spec_item" . $j . "_id"] = $d['n_spec' . $j][$i];
                        $updateData["ticket_number"] = $d['n_ticket_number'][$i] ?? null;

                    }
                }
                //  dd($updateData);
                $sid = ProductStyle::createStyle($id, $updateData, 1, $d['n_estimated_cost'][$i]);
                SaleChannel::changePrice($sale_id, $sid, $d['n_dealer_price'][$i], $d['n_price'][$i], $d['n_origin_price'][$i], $d['n_bonus'][$i], $d['n_dividend'][$i]);

            }
        }

        if ($d['del_id']) {
            ProductStyle::whereIn('id', explode(',', $d['del_id']))->where('product_id', $id)->delete();
        }

        if (isset($d['add_sku']) && $d['add_sku']) {
            ProductStyle::createSkuByProductId($id);
        }

        wToast('修改完成');
        return redirect(route('cms.product.edit-style', ['id' => $id]));

    }

    // 產生SKU
    public function createAllSku(Request $request, $id)
    {
        $styles = ProductStyle::where('product_id', $id)->whereNull('sku')->select('id')->get()->toArray();

        foreach ($styles as $style) {
            ProductStyle::createSku($id, $style['id']);
        }

        wToast('sku產生完成');
        return redirect()->back();
    }

    /**
     * 編輯 - 規格款式 - 編輯規格
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editSpec($id)
    {

        $product = self::product_data($id);
        //    dd( ProductSpec::specList($id));
        return view('cms.commodity.product.spec-edit', [
            'data' => Product::where('id', $id)->get()->first(),
            'specs' => ProductSpec::get()->toArray(),
            'currentSpec' => ProductSpec::specList($id),
            'product' => $product,
            'breadcrumb_data' => $product,
        ]);
    }

    public function storeSpec(Request $request, $id)
    {

        $vali_rule = [];
        for ($i = 0; $i < 3; $i++) {
            $vali_rule["item_new$i.*"] = ['required', 'regex:/^[^\'\"]*$/'];
            $vali_rule["item_value$i.*"] = ['required', 'regex:/^[^\'\"]*$/'];
        }
        $request->validate($vali_rule);

        $d = $request->all();
        for ($i = 0; $i < 3; $i++) {
            if (isset($d["spec" . $i])) {
                Product::setProductSpec($id, $d["spec" . $i]);
                // new
                if (isset($d["item_new" . $i]) && is_array($d["item_new" . $i])) {
                    foreach ($d["item_new" . $i] as $item) {
                        if ($item != '') {
                            ProductSpecItem::createItems($id, $d["spec" . $i], $item);
                        }
                    }
                }
                // update
                if (isset($d["item_id" . $i]) && is_array($d["item_id" . $i])) {
                    foreach ($d["item_id" . $i] as $key => $item_id) {
                        if (isset($d["item_value" . $i][$key]) && $d["item_value" . $i][$key] != '') {
                            ProductSpecItem::where('id', $item_id)->update([
                                'title' => $d["item_value" . $i][$key],
                            ]);
                        }
                    }
                }
            }
        }

        if (isset($d['del_item_id'])) {
            $del_item_id = explode(',', $d['del_item_id']);
            if (count($del_item_id) > 0) {
                ProductSpecItem::whereIn('id', $del_item_id)->delete();
            }
        }

        wToast('修改完成');

        return redirect(Route('cms.product.edit-style', ['id' => $id]));
    }

    /**
     * 編輯 - 銷售控管
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editSale($id)
    {

        $product = self::product_data($id);
        $specList = ProductSpec::specList($id);
        $styles = ProductStyle::where('product_id', $id)->get()->toArray();

        return view('cms.commodity.product.sales', [
            'product' => $product,
            'specList' => $specList,
            'styles' => $styles,
            'breadcrumb_data' => $product,
        ]);
    }
    /**
     * 編輯 - 銷售控管 - 庫存管理
     *
     * @param  int  $id 商品id
     * @param  int  $sid 款式id
     * @return \Illuminate\Http\Response
     */
    public function editStock($id, $sid)
    {

        $product = Product::productList(null, $id, ['user' => true, 'supplier' => true])->get()->first();
        $style = ProductStyle::where('id', $sid)->get()->first();
        if (!$product || !$style) {
            return abort(404);
        }

        $style->spec_titles = self::_getStyleTitle($style);

        $stocks = SaleChannel::styleStockList($sid)->get()->toArray();

        $notCompleteDeliverys = SaleChannel::notCompleteDelivery($sid)->get()->toArray();
        return view('cms.commodity.product.sales-stock', [
            'product' => $product,
            'style' => $style,
            'breadcrumb_data' => $product,
            'stocks' => $stocks,
            'notCompleteDeliverys' => $notCompleteDeliverys,
        ]);
    }
    /**
     * 編輯 - 銷售控管 - 庫存管理(儲存)
     *
     * @param  int  $id 商品id
     * @param  int  $sid 款式id
     * @return \Illuminate\Http\Response
     */
    public function updateStock(Request $request, $id, $sid)
    {
        $request->validate([
            'safety_stock' => 'required|numeric',
            'overbought' => 'required|numeric',
        ]);

        $d = request()->all();
        $sale_id = Arr::get($d, 'sale_id', []);
        $qty = Arr::get($d, 'qty', []);
        $re = ProductStyle::stockProcess($sid, $d['safety_stock'], $d['overbought'], $sale_id, $qty);

        if (!$re['success']) {
            return redirect()->back()->withErrors(['status' => $re['error_msg']]);
        }
        wToast('修改完成');
        return redirect()->back();
    }
    /**
     * 編輯 - 銷售控管 - 價格管理
     *
     * @param  int  $id 商品id
     * @param  int  $sid 款式id
     * @return \Illuminate\Http\Response
     */
    public function editPrice($id, $sid)
    {
        // dd('aa');

        $product = Product::productList(null, $id, ['user' => true, 'supplier' => true])->get()->first();

        $style = ProductStyle::where('id', $sid)->get()->first();
        if (!$product || !$style) {
            return abort(404);
        }

        $style->spec_titles = self::_getStyleTitle($style);

        $sales = SaleChannel::stylePriceList($sid)->get()->toArray();

        // dd($sales);
        return view('cms.commodity.product.sales-price', [
            'product' => $product,
            'style' => $style,
            'breadcrumb_data' => $product,
            'sales' => $sales,
        ]);
    }

    /**
     * 編輯 - 銷售控管 - 價格管理(儲存)
     *
     * @param  int  $id 商品id
     * @param  int  $sid 款式id
     * @return \Illuminate\Http\Response
     */
    public function updatePrice(Request $request, $id, $sid)
    {
        $d = request()->all();

        if (isset($d['sale_channel_id'])) {
            for ($i = 0; $i < count($d['sale_channel_id']); $i++) {
                SaleChannel::changePrice($d['sale_channel_id'][$i], $sid, $d['dealer_price'][$i], $d['price'][$i],
                    $d['origin_price'][$i], $d['bonus'][$i], $d['dividend'][$i]);
            }
        }

        wToast('修改完成');
        return redirect()->back();
    }

    public function batchProductStylePrice(Request $request, $id, $sid)
    {
        SaleChannel::batchProductStylePrice($sid);
        wToast('修改完成');
        return redirect()->back();
    }

    private static function _getStyleTitle($style)
    {
        if ($style->type == 'p') {
            $spec_titles = [];
            for ($i = 1; $i <= 3; $i++) {
                if ($style->{"spec_item" . $i . "_title"}) {
                    $spec_titles[] = $style->{"spec_item" . $i . "_title"};
                }
            }
        } else {
            $spec_titles[] = $style->title;
        }
        return $spec_titles;
    }

    /**
     * 編輯 - [網頁]商品介紹
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editWebDesc(Request $request, $id)
    {
        $name = $request->user()->name;
        $product = self::product_data($id);
        return view('cms.commodity.product.web_desciption', [
            'product' => $product,
            'breadcrumb_data' => $product,
            'desc' => $product->desc,
            'name' => $name,
        ]);
    }

    public function updateWebDesc(Request $request, $id)
    {

        $desc = $request->input('desc');

        if (!$desc) {
            $desc = '';
        }
        Product::where('id', $id)->update(['desc' => $desc]);

        wToast('儲存完畢');

        return redirect()->back();

    }

    /**
     * 編輯 - [網頁]規格說明
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editWebSpec($id)
    {

        $product = self::product_data($id);
        $lists = ProductSpecList::where('product_id', $id)->orderBy('sort')->get();

        if (count($lists) == 0) {
            $lists = [['title' => '', 'content' => '']];
        }

        return view('cms.commodity.product.web_spec', [
            'product' => $product,
            'lists' => $lists,
            'breadcrumb_data' => $product,
        ]);
    }

    public function updateWebSpec(Request $request, $id)
    {

        $d = $request->all();

        $items = [];
        if (isset($d['title']) && isset($d['content'])) {
            foreach ($d['title'] as $key => $title) {
                if ($title && $d['content'][$key]) {
                    $items[] = ['product_id' => $id, 'sort' => $key, 'title' => $title, 'content' => $d['content'][$key]];
                }
            }
        }

        ProductSpecList::updateItems($id, $items);
        wToast('儲存成功');
        return redirect()->back();
    }

    /**
     * 編輯 - [網頁]運送方式
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editWebLogis($id)
    {

        $product = self::product_data($id);
        return view('cms.commodity.product.web_logistics', [
            'product' => $product,
            'breadcrumb_data' => $product,
        ]);
    }

    public function updateWebLogis(Request $request, $id)
    {
        $desc = $request->input('logistic_desc');

        if (!$desc) {
            $desc = '';
        }

        Product::where('id', $id)->update(['logistic_desc' => $desc]);

        wToast('儲存完畢');

        return redirect()->back();

    }

    /**
     * 編輯 - 設定
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editSetting($id)
    {

        $product = self::product_data($id);

        $collection_ids = array_map(function ($n) {
            return $n->collection_id;
        }, Product::getCollectionsOfProduct($id)->get()->toArray());

        $currentShipment = array_map(function ($n) {
            return $n->group_id;
        }, Product::shipmentList($id)->get()->toArray());

        $currentPickup = array_map(function ($n) {
            return $n->depot_id_fk;
        }, Product::pickupList($id)->get()->toArray());

        return view('cms.commodity.product.settings', [
            'product' => $product,
            'breadcrumb_data' => $product,
            'allPickup' => Depot::getAllSelfPickup(),
            'currentPickup' => $currentPickup,
            'shipments' => ShipmentCategory::categoryWithGroup(),
            'currentShipment' => $currentShipment,
            'collections' => Collection::get(),
            'collection_ids' => $collection_ids,
        ]);
    }

    public function updateSetting(Request $request, $id)
    {
        // 檢查商品類型
        $product = Product::find($id);
        $tikType = TikType::find($product->tik_type_id);
        $tikTypeCode = $tikType ? $tikType->code : '';

        // 電子票券不可修改物流綁定
        if ($tikTypeCode != 'general') {
            // 取得商品目前 prd_product_shipment 的 group_id
            $currentShipment = Product::shipmentList($id)->pluck('group_id')->toArray();
            // 取得商品目前的物流綁定資料
            $currentPickup = Product::pickupList($id)->pluck('depot_id_fk')->toArray();

            // 比對前端傳入的新物流資料
            $newGroup_ids = $request->input('group_id');
            $newGroup_ids = array_map('intval', array_filter($newGroup_ids));
            $shipmentDiff = array_diff($currentShipment, $newGroup_ids) || array_diff($newGroup_ids, $currentShipment);
            $newDepotIds = $request->input('depot_id', []);


            if ($shipmentDiff || $currentPickup != $newDepotIds) {
                wToast('電子票券不可修改物流綁定', ['type' => 'danger']);
                return redirect()->back()->withErrors(['error' => '電子票券不可修改物流綁定']);
            }
        }

        $d = $request->all();
        $collection = isset($d['collection']) ? $d['collection'] : [];
        $recommend_collection_id = isset($d['recommend_collection_id']) ? $d['recommend_collection_id'] : null;
        $only_show_category = isset($d['only_show_category']) ? $d['only_show_category'] : 0;

        Collection::addProductToCollections($id, $collection);
        $update_arr = [];
        if (isset($recommend_collection_id)) {
            $update_arr['recommend_collection_id'] = $recommend_collection_id;
        }
        if (isset($only_show_category)) {
            $update_arr['only_show_category'] = $only_show_category;
        }
        Product::where('id', $id)->update($update_arr);

        foreach ($d['category_id'] as $key => $value) {
            Product::changeShipment($id, $value, $d['group_id'][$key]);
        }

        $depot_ids = $d['depot_id'] ?? [];
        Product::changePickup($id, $depot_ids);

        wToast('儲存完畢');
        return redirect()->back();

        //changeShipment
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * 組合包管理
     *
     * @return \Illuminate\Http\Response
     */
    public function editCombo($id)
    {

        $product = self::product_data($id);
        $styles = ProductStyle::styleList($id)->get()->toArray();

        return view('cms.commodity.product.combo', [
            'product' => $product,
            'styles' => $styles,
            'breadcrumb_data' => $product,
        ]);
    }

    public function updateCombo(Request $request, $id)
    {

        $d = $request->all();
        $sale_id = (SaleChannel::where('code', '01')->select('id')->get()->first())->id;

        if (isset($d['sid'])) {
            for ($i = 0; $i < count($d['sid']); $i++) {
                //  if (isset($d['sold_out_event'][$i])) {
                //    dd($d['sold_out_event'][$i]);
                //  ProductStyle::where('id', $d['sid'][$i])->update(['sold_out_event' => $d['sold_out_event'][$i]]);

                SaleChannel::changePrice($sale_id, $d['sid'][$i], $d['dealer_price'][$i], $d['price'][$i], $d['origin_price'][$i], $d['bonus'][$i], $d['dividend'][$i]);
                //   }
            }
        }

        //  dd('aaa');
        if (isset($d['active_id'])) {
            $queryStyleChildIds = Product::where('prd_products.id', $id)
                ->whereNull('prd_product_styles.deleted_at')
                ->leftJoin('prd_product_styles', 'prd_products.id', '=', 'prd_product_styles.product_id')
                ->leftJoin('prd_style_combos', 'prd_style_combos.product_style_id', '=', 'prd_product_styles.id')
                ->select('prd_style_combos.product_style_child_id')
                ->get()
                ->pluck('product_style_child_id')
                ->toArray();
            // $queryStyleChildIds 組合包內的商品類型需一致
            if (1 < count($queryStyleChildIds)) {
                $isSameTikType = ProductStyle::isSameTikTypeWithStyleIds($queryStyleChildIds);
                if (!$isSameTikType) {
                    wToast('組合包內的商品類型需一致', ['type' => 'danger']);
                    return redirect()->back();
                }
            }
            ProductStyle::activeStyle($id, $d['active_id']);
        }

        if (isset($d['del_id']) && $d['del_id']) {
            ProductStyle::whereIn('id', explode(',', $d['del_id']))->whereNull('sku')->delete();
        }

        if (isset($d['add_sku']) && $d['add_sku']) {
            ProductStyle::createSkuByProductId($id);
        }

        wToast('儲存完畢');
        return redirect()->back();

    }

    /**
     * 編輯組合包
     *
     * @return \Illuminate\Http\Response
     */
    public function editComboProd($id, $sid)
    {

        $style = ProductStyle::where('id', $sid)->get()->first();
        $product = self::product_data($id);
        return view('cms.commodity.product.combo-edit', [
            'data' => $style,
            'product' => $product,
            'combos' => ProductStyleCombo::comboList($sid)->get(),
            'method' => 'create',
            'formAction' => Route('cms.product.edit-combo-prod', ['id' => $id, 'sid' => $sid]),
            'breadcrumb_data' => ['product' => $product,
                'style' => $style],
        ]);
    }

    /**
     * 編輯組合包
     *
     * @return \Illuminate\Http\Response
     */
    public function updateComboProd(Request $request, $id, $sid)
    {

        if (ProductStyle::where('id', $sid)->whereNotNull('sku')->get()->first()) {
            return redirect()->back();
        }

        $request->validate([
            'title' => 'required',
            'style_id' => 'array',
            'ps_qty' => 'array',
            'o_style_id' => 'array',
            'o_ps_qty' => 'array',
        ]);
        $d = request()->all();

        if (isset($d['style_id'])) {
            for ($i = 0; $i < count($d['style_id']); $i++) {
                if ($d['ps_qty'][$i]) {
                    ProductStyleCombo::createCombo($sid, $d['style_id'][$i], $d['ps_qty'][$i]);
                }
            }
        }

        if (isset($d['o_style_id'])) {
            for ($i = 0; $i < count($d['o_style_id']); $i++) {
                if ($d['o_ps_qty'][$i]) {
                    ProductStyleCombo::where('id', $d['o_style_id'][$i])
                        ->update(['qty' => $d['o_ps_qty'][$i]]);
                }
            }
        }

        ProductStyle::where('id', $sid)->update(['title' => $d['title']]);

        if (isset($d['del_item_id'])) {
            ProductStyleCombo::whereIn('id', explode(',', $d['del_item_id']))->delete();
        }

        ProductStyleCombo::estimatedCost($sid);

        wToast('儲存完畢');
        return redirect(Route('cms.product.edit-combo', ['id' => $id]));

    }

    /**
     * 新增組合包
     *
     * @return \Illuminate\Http\Response
     */
    public function createComboProd($id)
    {
        $product = self::product_data($id);
        return view('cms.commodity.product.combo-edit', [
            'product' => $product,
            'combos' => [],
            'method' => 'create',
            'formAction' => Route('cms.product.create-combo-prod', ['id' => $id]),
            'breadcrumb_data' => $product,
        ]);
    }

    /**
     * 新增組合包
     *
     * @return \Illuminate\Http\Response
     */
    public function storeComboProd(Request $request, $id)
    {

        // dd($_POST);
        $request->validate([
            'title' => 'required',
            'style_id' => 'array',
            'ps_qty' => 'array',
        ]);
        $d = request()->all();

        $inputStyleIds = isset($d['style_id']) ? array_map('intval', $d['style_id']) : [];

        $queryStyleChildIds = Product::where('prd_products.id', $id)
            ->whereNull('prd_product_styles.deleted_at')
            ->leftJoin('prd_product_styles', 'prd_products.id', '=', 'prd_product_styles.product_id')
            ->leftJoin('prd_style_combos', 'prd_style_combos.product_style_id', '=', 'prd_product_styles.id')
            ->select('prd_style_combos.product_style_child_id')
            ->get()
            ->pluck('product_style_child_id')
            ->toArray();
        // 合併兩者，並去除重複值
        $mergedStyleIds = array_unique(array_merge($inputStyleIds, $queryStyleChildIds));
        // 若合併後款式數量超過 1，但款式類型不一致，即提示錯誤並返回
        if (count($mergedStyleIds) > 1) {
            if (!ProductStyle::isSameTikTypeWithStyleIds($mergedStyleIds)) {
                wToast('組合包內的商品類型需一致', ['type' => 'danger']);
                return redirect()->back();
            }
            $queryTikTypeIds = ProductStyle::whereIn('prd_product_styles.id', $mergedStyleIds)
            ->leftJoin('prd_products', 'prd_products.id', '=', 'prd_product_styles.product_id')
            ->select('prd_products.tik_type_id')
            ->distinct()
            ->get()
            ->pluck('tik_type_id')
            ->toArray();
            if (1 <= count($queryTikTypeIds)) {
                $product = Product::find($id);
                // 判斷 $product.tikt_type_id 是否和 $queryTikTypeIds 裡的值完全一致
                if (!in_array($product->tik_type_id, $queryTikTypeIds)) {
                    wToast('組合包商品類型與組合包內的商品類型需一致', ['type' => 'danger']);
                    return redirect()->back();
                }
            }
        }


        $sid = ProductStyle::createComboStyle($id, $d['title'], 1);
        if (isset($d['style_id'])) {
            for ($i = 0; $i < count($d['style_id']); $i++) {
                if ($d['ps_qty'][$i]) {
                    ProductStyleCombo::createCombo($sid, $d['style_id'][$i], $d['ps_qty'][$i]);
                }
            }
        }

        ProductStyleCombo::estimatedCost($sid);

        return redirect(Route('cms.product.edit-combo', ['id' => $id]));

    }

    public function show(Request $request, $id)
    {

        $product = Product::where('id', $id)->get()->first();
        if (!$product) {
            return abort(404);
        }

        $product_img = ProductImg::where('product_id', $id)->get()->toArray();
        $product_spec_list = ProductSpecList::where('product_id', $id)->get();
        $styles = ProductStyle::getStylePrice($id);

        $shipment = DB::table('prd_product_shipment as ps')
            ->leftJoin('shi_group as group', 'ps.group_id', '=', 'group.id')
            ->select('group.note')
            ->where('ps.product_id', '=', $id)->get()->first();

        // dd($shipment);

        return view('cms.commodity.product.show', [
            'product' => $product,
            'images' => $product_img,
            'specs' => $product_spec_list,
            'styles' => $styles,
            'shipment' => $shipment,
            'breadcrumb_data' => $product,
        ]);
    }

    function clone (Request $request, $id) {
        $request->validate([
            'product_id' => 'required',
        ]);

        Product::cloneInfo($id, $request->input('product_id'));

        wToast('資訊複製完成');

        return redirect()->back();

    }

    /**
     * EXCEL匯出:欄位
     * 商品名稱 / 廠商名稱 / 物流名稱 /物流方式 / 公開狀態/ 負責人
     * 物流方式為 喜鴻出貨或廠商出貨唷
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export_excel()
    {
        $data = [];
        $dataList = DB::table('prd_products')
            ->leftJoin('prd_product_supplier', 'prd_product_supplier.product_id', '=', 'prd_products.id')
            ->leftJoin('prd_suppliers', 'prd_product_supplier.supplier_id', '=', 'prd_suppliers.id')
            ->leftJoin('usr_users', 'prd_products.user_id', '=', 'usr_users.id')
            ->leftJoin('prd_product_shipment', 'prd_product_shipment.product_id', '=', 'prd_products.id')
            ->leftJoin('shi_group', 'prd_product_shipment.group_id', '=', 'shi_group.id')
            ->leftJoin('shi_method', 'shi_group.method_fk', '=', 'shi_method.id')
            ->whereNotNull('shi_method.id')
            ->select([
                'prd_products.sku',
                'prd_products.title',
                'prd_suppliers.name AS supplier',
                'shi_group.name AS shipment_name',
                'shi_method.method',
                'usr_users.name AS user_name',
            ])
            ->selectRaw('CASE prd_products.public WHEN 1 THEN "公開" WHEN 0 THEN "不公開" END AS public')
            ->get();
        foreach ($dataList as $item) {
            $data[] = [
                $item->sku,
                $item->title,
                $item->supplier,
                $item->shipment_name,
                $item->method,
                $item->public,
                $item->user_name,
            ];
        }

        $column_name = [
            'sku',
            '商品名稱',
            '廠商名稱',
            '物流名稱',
            '物流方式',
            '公開狀態',
            '負責人',
        ];
        $export = new ProductInforExport([
            $column_name,
            $data,
        ]);

        return Excel::download($export, 'product_infor.xlsx');
    }

    private static function imgResize($path)
    {
        try {
            $asdf = Image::make($path)
                ->resize(640, 640, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->encode('webp', 90);
        } catch (\Exception $e) {
            dd($e);
            return false;
        }
        return $asdf;
    }

    private static function imgFilename($product_id, $fileHashName)
    {
        return 'product_imgs/' . $product_id . '/' . explode('.', $fileHashName)[0] . ".webp";

    }
}
