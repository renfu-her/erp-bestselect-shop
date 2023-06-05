<?php
use Diglactic\Breadcrumbs\Breadcrumbs;

// This import is also not required, and you could replace `BreadcrumbTrail $trail`
//  with `$trail`. This is nice for IDE type checking and completion.
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Home
Breadcrumbs::for('cms.dashboard', function (BreadcrumbTrail $trail) {
    $trail->push('總覽', route('cms.dashboard'));
});


/**
 * Topbar
 **/

// 資料維護
Breadcrumbs::for('cms.usermnt.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('資料維護');
});

// 會員綁定
Breadcrumbs::for('cms.usermnt.customer-binding', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('會員綁定');
});


/**
 * 進銷存退
 **/

// 商品管理
Breadcrumbs::for('cms.product.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('商品管理', route('cms.product.index'));
});
// 新增商品
Breadcrumbs::for('cms.product.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.product.index');
    $trail->push('新增商品');
});
// 瀏覽 - 商品資訊
Breadcrumbs::for('cms.product.show', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.index');
    $trail->push('資訊-' . $value->title);
});
// 編輯 - 商品資訊
Breadcrumbs::for('cms.product.edit', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.index');
    $trail->push('編輯-' . $value->title);
});
// 編輯 - 規格款式
Breadcrumbs::for('cms.product.edit-style', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.index');
    $trail->push('規格款式-' . $value->title, route('cms.product.edit-style', $value->id));
});
Breadcrumbs::for('cms.product.edit-spec', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.edit-style', $value);
    $trail->push('編輯規格');
});
// 編輯 - 組合包
Breadcrumbs::for('cms.product.edit-combo', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.index');
    $trail->push('組合包款式-' . $value->title, route('cms.product.edit-combo', ['id' => $value->id]));
});
Breadcrumbs::for('cms.product.create-combo-prod', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.edit-combo', $value);
    $trail->push('新增組合包款式');
});
Breadcrumbs::for('cms.product.edit-combo-prod', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.edit-combo', $value['product']);
    $trail->push($value['style']->title);
});
// 編輯 - 銷售控管
Breadcrumbs::for('cms.product.edit-sale', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.index');
    $trail->push('銷售控管-' . $value->title, route('cms.product.edit-sale', $value->id));
});
// 編輯 - 銷售控管 - 庫存管理
Breadcrumbs::for('cms.product.edit-stock', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.edit-sale', $value);
    $trail->push('庫存管理');
});
// 編輯 - 銷售控管 - 價格管理
Breadcrumbs::for('cms.product.edit-price', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.edit-sale', $value);
    $trail->push('價格管理');
});
// 編輯 - 網頁-商品介紹
Breadcrumbs::for('cms.product.edit-web-desc', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.index');
    $trail->push('[網頁]商品介紹-' . $value->title);
});
// 編輯 - 網頁-規格說明
Breadcrumbs::for('cms.product.edit-web-spec', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.index');
    $trail->push('[網頁]規格說明-' . $value->title);
});
// 編輯 - 設定
Breadcrumbs::for('cms.product.edit-setting', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.index');
    $trail->push('設定-' . $value->title);
});

// 庫存管理
Breadcrumbs::for('cms.stock.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('庫存管理', route('cms.stock.index'));
});
// 庫存管理 - 明細
Breadcrumbs::for('cms.stock.stock_detail_log', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.stock.index');
    $trail->push($value);
});
// 庫存管理 - 待出貨列表
Breadcrumbs::for('cms.stock.dlv_qty', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.stock.index');
    $trail->push('待出貨列表', route('cms.stock.index'));
});
// 庫存管理 - 被組合數量
Breadcrumbs::for('cms.stock.stock_combo_detail', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.stock.index');
    $trail->push('元素被組合可售數量', route('cms.stock.index'));
});
// 報廢管理
Breadcrumbs::for('cms.scrap.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('報廢管理', route('cms.scrap.index'));
});
// 新增報廢單
Breadcrumbs::for('cms.scrap.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.scrap.index');
    $trail->push('新增報廢單');
});
// 編輯 - 報廢單資訊
Breadcrumbs::for('cms.scrap.edit', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.scrap.index');
    $trail->push('#' . $value['sn'] . ' 報廢單資訊', route('cms.scrap.edit', ['id' => $value['id']]));
});

// 採購單庫存匯入
Breadcrumbs::for('cms.inbound_import.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('採購單庫存匯入', route('cms.inbound_import.index'));
});
// 採購單庫存匯入紀錄
Breadcrumbs::for('cms.inbound_import.import_log', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.inbound_import.index');
    $trail->push('匯入紀錄');
});
// 入庫單列表
Breadcrumbs::for('cms.inbound_import.inbound_list', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.inbound_import.index');
    $trail->push('入庫單列表');
});
// 入庫單庫存調整
Breadcrumbs::for('cms.inbound_import.inbound_edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.inbound_import.index');
    $trail->push('入庫單列表', route('cms.inbound_import.inbound_list'));
    $trail->push('入庫單庫存調整');
});
// 入庫單調整紀錄
Breadcrumbs::for('cms.inbound_import.inbound_log', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.inbound_import.index');
    $trail->push('入庫單調整紀錄');
});

// 採購單管理
Breadcrumbs::for('cms.purchase.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('採購單管理', route('cms.purchase.index'));
});
// 新增採購單
Breadcrumbs::for('cms.purchase.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.purchase.index');
    $trail->push('新增採購單');
});
// 編輯 - 採購單資訊
Breadcrumbs::for('cms.purchase.edit', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.purchase.index');
    $trail->push('#' . $value['sn'] . ' 採購單資訊', route('cms.purchase.edit', ['id' => $value['id']]));
});
// 編輯 - 採購單資訊 - 新增訂金付款單
Breadcrumbs::for('cms.purchase.pay-deposit', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.purchase.edit', $value);
    $trail->push('新增訂金付款單');
});
// 編輯 - 採購單資訊 - 新增尾款付款單
// Breadcrumbs::for('cms.purchase.pay-final', function (BreadcrumbTrail $trail, $value) {
//     $trail->parent('cms.purchase.edit', $value);
//     $trail->push('新增尾款付款單');
// });
// 編輯 - 採購單資訊 - 新增付款單--訂金&&尾款
Breadcrumbs::for('cms.purchase.po-create', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.purchase.edit', $value);
    $trail->push($value['type'] == 0 ? '訂金付款單' : '尾款付款單', route('cms.purchase.view-pay-order', ['id' => $value['id'], 'type' => $value['type']]));
    $trail->push('新增付款');
});
//顯示訂單付款單
Breadcrumbs::for('cms.purchase.pay-order', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.purchase.edit', $value);
    // $trail->push('付款單');
    $trail->push($value['type'] == 0 ? '訂金付款單' : '尾款付款單');
});
Breadcrumbs::for('cms.purchase.view-pay-order', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.purchase.edit', $value);
    // $trail->push('付款單');
    $trail->push($value['type'] == 0 ? '訂金付款單' : '尾款付款單');
});
// 編輯 - 變更紀錄
Breadcrumbs::for('cms.purchase.log', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.purchase.index');
    $trail->push('#' . $value . ' 變更紀錄');
});
// 編輯 - 入庫審核
Breadcrumbs::for('cms.purchase.inbound', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.purchase.index');
    $trail->push('#' . $value . ' 入庫審核');
});
// 採購退出
Breadcrumbs::for('cms.purchase.return_list', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.purchase.index');
    $trail->push($value ? '#' . $value . ' 退出列表' : '退出列表');
});
// 新增採購退出單
Breadcrumbs::for('cms.purchase.return_create', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.purchase.index');
    $trail->push('#' . $value['purchase_sn'] . ' 退出列表', route('cms.purchase.return_list', ['purchase_id' => $value['purchase_id']]));
    $trail->push('新增退出單');
});
// 編輯採購退出單
Breadcrumbs::for('cms.purchase.return_edit', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.purchase.index');
    $trail->push('#' . $value['purchase_sn'] . ' 退出列表', route('cms.purchase.return_list', ['purchase_id' => $value['purchase_id']]));
    $trail->push('編輯退出單');
});
// 採購退出單明細
Breadcrumbs::for('cms.purchase.return_detail', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.purchase.index');
    $trail->push('#' . $value['purchase_sn'] . ' 退出列表', route('cms.purchase.return_list', ['purchase_id' => $value['purchase_id']]));
    $trail->push('採購退出單資訊');
});
// 採購退出單審核
Breadcrumbs::for('cms.purchase.return_inbound', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.purchase.index');
    $trail->push('#' . $value['purchase_sn'] . ' 退出列表', route('cms.purchase.return_list', ['purchase_id' => $value['purchase_id']]));
    $trail->push('採購退出單審核');
});
// 新增收款單
Breadcrumbs::for('cms.purchase.ro-edit', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.purchase.index');
    $trail->push('#' . $value['purchase_sn'] . ' 退出列表', route('cms.purchase.return_list', ['purchase_id' => $value['purchase_id']]));
    $trail->push('採購退出單資訊', route('cms.purchase.return_detail', ['return_id' => $value['return_id']]));
    $trail->push('新增收款單');
});
//顯示訂單收款單
Breadcrumbs::for('cms.purchase.ro-receipt', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.purchase.index');
    $trail->push('#' . $value['purchase_sn'] . ' 退出列表', route('cms.purchase.return_list', ['purchase_id' => $value['purchase_id']]));
    $trail->push('採購退出單資訊', route('cms.purchase.return_detail', ['return_id' => $value['return_id']]));
    $trail->push('收款單');
});
//編輯收款單入帳日期
Breadcrumbs::for('cms.purchase.ro-review', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.purchase.index');
    $trail->push('#' . $value['purchase_sn'] . ' 退出列表', route('cms.purchase.return_list', ['purchase_id' => $value['purchase_id']]));
    $trail->push('採購退出單資訊', route('cms.purchase.return_detail', ['return_id' => $value['return_id']]));
    $trail->push('收款單', route('cms.purchase.ro-receipt', ['return_id' => $value['return_id']]));
    $trail->push('入款審核');
});
//編輯收款單稅別/摘要/備註
Breadcrumbs::for('cms.purchase.ro-taxation', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.purchase.index');
    $trail->push('#' . $value['purchase_sn'] . ' 退出列表', route('cms.purchase.return_list', ['purchase_id' => $value['purchase_id']]));
    $trail->push('採購退出單資訊', route('cms.purchase.return_detail', ['return_id' => $value['return_id']]));
    $trail->push('收款單', route('cms.purchase.ro-receipt', ['return_id' => $value['return_id']]));
    $trail->push('修改摘要/稅別');
});

// 組合包組裝
Breadcrumbs::for('cms.combo-purchase.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('組合包組裝', route('cms.combo-purchase.index'));
});
Breadcrumbs::for('cms.combo-purchase.edit', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.combo-purchase.index');
    $trail->push($value['product']->title . ' - ' . $value['style']->title);
});

// 訂單管理
Breadcrumbs::for('cms.order.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('訂單管理', route('cms.order.index'));
});
Breadcrumbs::for('cms.order.detail', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value . ' 訂單明細');
});
Breadcrumbs::for('cms.order.create', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('新增訂單');
});
Breadcrumbs::for('cms.order.bonus-gross', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.order.detail', ['id' => $value['id']]));
    $trail->push('獎金毛利');
});
Breadcrumbs::for('cms.order.personal-bonus', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.order.detail', ['id' => $value['id']]));
    $trail->push('個人獎金');
});
Breadcrumbs::for('cms.order.split-order', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.order.detail', ['id' => $value['id']]));
    $trail->push('分割訂單');
});
Breadcrumbs::for('cms.order.edit-item', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.order.detail', ['id' => $value['id']]));
    $trail->push('編輯訂單');
});
Breadcrumbs::for('cms.order.order-flow', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.order.detail', ['id' => $value['id']]));
    $trail->push('訂單紀錄');
});
// 訂單自取入庫審核
Breadcrumbs::for('cms.order.inbound', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 入庫審核', route('cms.order.inbound', ['subOrderId' => $value['id']]));
});
// 新增收款單
Breadcrumbs::for('cms.order.ro-edit', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.order.detail', ['id' => $value['id']]));
    $trail->push('新增收款單');
});
//顯示訂單收款單
Breadcrumbs::for('cms.order.ro-receipt', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.order.detail', ['id' => $value['id']]));
    $trail->push('收款單');
});
//編輯收款單入帳日期
Breadcrumbs::for('cms.order.ro-review', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.order.detail', ['id' => $value['id']]));
    $trail->push('收款單', route('cms.order.ro-receipt', ['id' => $value['id']]));
    $trail->push('入款審核');
});
//編輯收款單稅別/摘要/備註
Breadcrumbs::for('cms.order.ro-taxation', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.order.detail', ['id' => $value['id']]));
    $trail->push('收款單', route('cms.order.ro-receipt', ['id' => $value['id']]));
    $trail->push('修改摘要/稅別');
});
// 新增發票
Breadcrumbs::for('cms.order.create-invoice', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.order.detail', ['id' => $value['id']]));
    $trail->push('開立發票');
});
// 顯示發票
Breadcrumbs::for('cms.order.show-invoice', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.order.detail', ['id' => $value['id']]));
    $trail->push('發票資訊');
});
// 編輯發票
Breadcrumbs::for('cms.order.edit-invoice', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.order.detail', ['id' => $value['id']]));
    $trail->push('編輯發票');
});
// 發票折讓
Breadcrumbs::for('cms.order.allowance-invoice', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.order.detail', ['id' => $value['id']]));
    $trail->push('發票資訊', $value['previous_url']);
    $trail->push('發票折讓');
});
// 編輯發票折讓
Breadcrumbs::for('cms.order.edit-allowance', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.order.detail', ['id' => $value['id']]));
    $trail->push('發票資訊', $value['previous_url']);
    $trail->push('編輯發票折讓');
});
// Line Pay 付款取消
Breadcrumbs::for('cms.order.line-pay-refund', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.order.detail', ['id' => $value['id']]));
    $trail->push('Line Pay 付款取消');
});
Breadcrumbs::for('cms.order.logistic-po', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.order.detail', ['id' => $value['id']]));
    $trail->push('物流付款單');
});
Breadcrumbs::for('cms.order.logistic-po-create', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.order.detail', ['id' => $value['id']]));
    $trail->push('物流付款單', route('cms.order.logistic-po', ['id' => $value['id'], 'sid' => $value['sid']]));
    $trail->push('新增付款');
});
Breadcrumbs::for('cms.order.return-pay-order', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.order.detail', ['id' => $value['id']]));
    $trail->push('退貨付款單');
});
Breadcrumbs::for('cms.order.return-pay-create', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.order.detail', ['id' => $value['id']]));
    $trail->push('退貨付款單', route('cms.order.return-pay-order', ['id' => $value['id'], 'sid' => $value['sid']]));
    $trail->push('新增付款');
});

// 出貨管理
Breadcrumbs::for('cms.delivery.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('出貨管理', route('cms.delivery.index'));
});

// 出貨商品查詢
Breadcrumbs::for('cms.delivery_product.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('出貨商品查詢', route('cms.delivery_product.index'));
});


/**
 * 寄倉管理
 **/

// 寄倉搜尋
Breadcrumbs::for('cms.consignment.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('寄倉搜尋', route('cms.consignment.index'));
});
// 新增寄倉單
Breadcrumbs::for('cms.consignment.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.consignment.index');
    $trail->push('新增寄倉單');
});
// 編輯 - 寄倉單資訊
Breadcrumbs::for('cms.consignment.edit', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.consignment.index');
    $trail->push('#' . $value['sn'] . ' 寄倉單資訊', route('cms.consignment.edit', ['id' => $value['id']]));
});
Breadcrumbs::for('cms.consignment.logistic-po', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.consignment.index');
    $trail->push('#' . $value['sn'] . ' 寄倉單資訊', route('cms.consignment.edit', ['id' => $value['id']]));
    $trail->push('運費付款單');
});
Breadcrumbs::for('cms.consignment.logistic-po-create', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.consignment.index');
    $trail->push('#' . $value['sn'] . ' 寄倉單資訊', route('cms.consignment.edit', ['id' => $value['id']]));
    $trail->push('運費付款單', route('cms.consignment.logistic-po', ['id' => $value['id']]));
    $trail->push('新增付款');
});
// 寄倉入庫審核
Breadcrumbs::for('cms.consignment.inbound', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.consignment.index');
    $trail->push('#' . $value['sn'] . ' 入庫審核', route('cms.consignment.inbound', ['id' => $value['id']]));
});

//寄倉訂購
Breadcrumbs::for('cms.consignment-order.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('寄倉訂購', route('cms.consignment-order.index'));
});
// 新增寄倉訂購單
Breadcrumbs::for('cms.consignment-order.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.consignment-order.index');
    $trail->push('新增寄倉訂購單');
});
// 編輯 - 寄倉訂購單資訊
Breadcrumbs::for('cms.consignment-order.edit', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.consignment-order.index');
    $trail->push('#' . $value['sn'] . ' 寄倉訂購單', route('cms.consignment-order.edit', ['id' => $value['id']]));
});
// 新增收款單
Breadcrumbs::for('cms.ar_csnorder.create', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.consignment-order.index');
    $trail->push('#' . $value['sn'] . ' 寄倉訂購單', route('cms.consignment-order.edit', ['id' => $value['id']]));
    $trail->push('新增收款單');
});
//顯示訂單收款單
Breadcrumbs::for('cms.ar_csnorder.receipt', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.consignment-order.index');
    $trail->push('#' . $value['sn'] . ' 寄倉訂購單', route('cms.consignment-order.edit', ['id' => $value['id']]));
    $trail->push('收款單');
});
//編輯收款單入帳日期
Breadcrumbs::for('cms.ar_csnorder.review', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.consignment-order.index');
    $trail->push('#' . $value['sn'] . ' 寄倉訂購單', route('cms.consignment-order.edit', ['id' => $value['id']]));
    $trail->push('收款單', route('cms.ar_csnorder.receipt', ['id' => $value['id']]));
    $trail->push('入款審核');
});
//編輯收款單稅別/摘要/備註
Breadcrumbs::for('cms.ar_csnorder.taxation', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.consignment-order.index');
    $trail->push('#' . $value['sn'] . ' 寄倉訂購單', route('cms.consignment-order.edit', ['id' => $value['id']]));
    $trail->push('收款單', route('cms.ar_csnorder.receipt', ['id' => $value['id']]));
    $trail->push('修改摘要/稅別');
});

//寄倉庫存
Breadcrumbs::for('cms.consignment-stock.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('寄倉庫存', route('cms.consignment-stock.index'));
});
// 寄倉庫存明細
Breadcrumbs::for('cms.consignment-stock.stock_detail_log', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.consignment-stock.index');
    $trail->push('#' . $value . ' 明細');
});


/**
 * 報表
 **/

// 業績報表
Breadcrumbs::for('cms.user-performance-report.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('業績報表', route('cms.user-performance-report.index'));
});

Breadcrumbs::for('cms.user-performance-report.department', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.user-performance-report.index');
    $trail->push('部門');
});

Breadcrumbs::for('cms.user-performance-report.group', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.user-performance-report.department');
    $trail->push('組');
});

Breadcrumbs::for('cms.user-performance-report.user', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.user-performance-report.group');
    $trail->push('人員');
});

// 採購營收報表
Breadcrumbs::for('cms.product-manager-report.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('採購營收報表', route('cms.product-manager-report.index'));
});
Breadcrumbs::for('cms.product-manager-report.product', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.product-manager-report.index');
    $trail->push('商品報表');
});

//售價利潤報表
Breadcrumbs::for('cms.product-profit-report.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('售價利潤報表');
});

// 營業額目標
Breadcrumbs::for('cms.vob-performance-report.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('營業額目標');
});

// 季報表
Breadcrumbs::for('cms.product-report.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('季報表');
});


/**
 * 行銷設定
 **/

// 優惠劵到期通知
Breadcrumbs::for('cms.discount_expiring.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('優惠劵到期通知');
});
Breadcrumbs::for('cms.discount_expiring.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('優惠劵到期通知', route('cms.discount_expiring.index'));
    $trail->push('編輯優惠劵到期通知信');
});

// 優惠劵 / 代碼
Breadcrumbs::for('cms.promo.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('優惠券 / 代碼', route('cms.promo.index'));
});
// 新增 優惠劵 / 代碼
Breadcrumbs::for('cms.promo.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.promo.index');
    $trail->push('新增優惠劵 / 代碼');
});
// 編輯 優惠劵 / 代碼
Breadcrumbs::for('cms.promo.edit', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.promo.index');
    $trail->push($value);
});

// 全館優惠
Breadcrumbs::for('cms.discount.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('全館優惠', route('cms.discount.index'));
});
// 新增全館優惠
Breadcrumbs::for('cms.discount.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.discount.index');
    $trail->push('新增全館優惠');
});
// 編輯 全館優惠
Breadcrumbs::for('cms.discount.edit', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.discount.index');
    $trail->push($value);
});

// 通關優惠券
Breadcrumbs::for('cms.coupon-event.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('通關優惠券', route('cms.coupon-event.index'));
});

Breadcrumbs::for('cms.coupon-event.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.coupon-event.index');
    $trail->push('編輯');
});

Breadcrumbs::for('cms.coupon-event.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.coupon-event.index');
    $trail->push('新增');
});

//縮短網址產生器
Breadcrumbs::for('cms.utm-url.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('縮短網址產生器', route('cms.utm-url.index'));
});

// EDM
Breadcrumbs::for('cms.edm.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('EDM');
});

// 一頁式網站
Breadcrumbs::for('cms.onepage.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('一頁式網站');
});
Breadcrumbs::for('cms.onepage.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.onepage.index');
    $trail->push('新增');
});
Breadcrumbs::for('cms.onepage.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.onepage.index');
    $trail->push('修改');
});

// 手動發放紅利
Breadcrumbs::for('cms.manual-dividend.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('手動發放紅利');
});
Breadcrumbs::for('cms.manual-dividend.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.manual-dividend.index');
    $trail->push('新增');
});
Breadcrumbs::for('cms.manual-dividend.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.manual-dividend.index');
    $trail->push('修改');
});
Breadcrumbs::for('cms.manual-dividend.show', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.manual-dividend.index');
    $trail->push('明細');
});


/**
 * 設定
 **/

//款式設定
Breadcrumbs::for('cms.spec.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('款式設定', route('cms.spec.index'));
});
Breadcrumbs::for('cms.spec.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.spec.index');
    $trail->push('新增');
});
Breadcrumbs::for('cms.spec.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.spec.index');
    $trail->push('編輯');
});

// 商品類別
Breadcrumbs::for('cms.category.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('商品類別', route('cms.category.index'));
});
Breadcrumbs::for('cms.category.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.category.index');
    $trail->push('新增');
});
Breadcrumbs::for('cms.category.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.category.index');
    $trail->push('編輯');
});

// 倉庫管理
Breadcrumbs::for('cms.depot.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('倉庫管理', route('cms.depot.index'));
});
Breadcrumbs::for('cms.depot.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.depot.index');
    $trail->push('新增');
});
Breadcrumbs::for('cms.depot.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.depot.index');
    $trail->push('編輯');
});
Breadcrumbs::for('cms.depot.product-index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.depot.index');
    $trail->push('寄倉選品');
});
Breadcrumbs::for('cms.depot.product-create', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('cms.depot.index');
    $trail->push('寄倉選品', route('cms.depot.product-index', ['id' => $id]));
    $trail->push('選品');
});
Breadcrumbs::for('cms.depot.product-edit', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('cms.depot.index');
    $trail->push('寄倉選品', route('cms.depot.product-index', ['id' => $id]));
    $trail->push('修改');
});

// 廠商管理
Breadcrumbs::for('cms.supplier.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('廠商管理', route('cms.supplier.index'));
});
Breadcrumbs::for('cms.supplier.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.supplier.index');
    $trail->push('新增');
});
Breadcrumbs::for('cms.supplier.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.supplier.index');
    $trail->push('編輯');
});

// 銷售通路管理
Breadcrumbs::for('cms.sale_channel.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('銷售通路管理', route('cms.sale_channel.index'));
});
Breadcrumbs::for('cms.sale_channel.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.sale_channel.index');
    $trail->push('新增');
});
Breadcrumbs::for('cms.sale_channel.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.sale_channel.index');
    $trail->push('編輯');
});

// 物流運費管理
Breadcrumbs::for('cms.shipment.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('物流運費管理', route('cms.shipment.index'));
});
Breadcrumbs::for('cms.shipment.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.shipment.index');
    $trail->push('新增');
});
Breadcrumbs::for('cms.shipment.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.shipment.index');
    $trail->push('編輯');
});

Breadcrumbs::for('cms.shipment.method-edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.shipment.index');
    $trail->push('出貨方式');
});

// 團購主公司管理
Breadcrumbs::for('cms.groupby-company.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('團購主公司管理', route('cms.groupby-company.index'));
});
Breadcrumbs::for('cms.groupby-company.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.groupby-company.index');
    $trail->push('新增');
});
Breadcrumbs::for('cms.groupby-company.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.groupby-company.index');
    $trail->push('編輯');
});

// 通知信管理
Breadcrumbs::for('cms.mail_set.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('通知信管理', route('cms.mail_set.index'));
});

// 組織架構
Breadcrumbs::for('cms.organize.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('組織架構', route('cms.organize.index'));
});
Breadcrumbs::for('cms.organize.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.organize.index');
    $trail->push('編輯');
});

// 企業網管理
Breadcrumbs::for('cms.b2e-company.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('企業網管理', route('cms.b2e-company.index'));
});
Breadcrumbs::for('cms.b2e-company.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.b2e-company.index');
    $trail->push('新增');
});
Breadcrumbs::for('cms.b2e-company.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.b2e-company.index');
    $trail->push('編輯');
});

// 團控查詢帳號
Breadcrumbs::for('cms.erp-travel.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('團控查詢帳號');
});
Breadcrumbs::for('cms.erp-travel.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.erp-travel.index');
    $trail->push('編輯');
});
Breadcrumbs::for('cms.erp-travel.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.erp-travel.index');
    $trail->push('新增');
});


/**
 * 官網設定
 **/

//首頁設定-導覽列
Breadcrumbs::for('cms.homepage.navbar.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('首頁設定-導覽列', route('cms.homepage.navbar.index'));
});
//首頁設定-橫幅廣告
Breadcrumbs::for('cms.homepage.banner.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('首頁設定-橫幅廣告', route('cms.homepage.banner.index'));
});
Breadcrumbs::for('cms.homepage.banner.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.homepage.banner.index');
    $trail->push('新增');
});
Breadcrumbs::for('cms.homepage.banner.edit', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.homepage.banner.index');
    $trail->push($value);
});
//首頁設定-版型
Breadcrumbs::for('cms.homepage.template.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('首頁設定-版型', route('cms.homepage.template.index'));
});
Breadcrumbs::for('cms.homepage.template.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.homepage.template.index');
    $trail->push('新增');
});
Breadcrumbs::for('cms.homepage.template.edit', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.homepage.template.index');
    $trail->push($value);
});

//商品群組
Breadcrumbs::for('cms.collection.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('商品群組', route('cms.collection.index'));
});
Breadcrumbs::for('cms.collection.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.collection.index');
    $trail->push('新增');
});
Breadcrumbs::for('cms.collection.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.collection.index');
    $trail->push('編輯');
});

// 選單列表設定
Breadcrumbs::for('cms.navinode.index', function ($trail) {
    $trail->parent('cms.dashboard');
    $trail->push('選單列表設定', route('cms.navinode.index'));
    /*
    foreach ($value as $v) {
    $trail->push($v['title'], route('cms.navinode.index', ['level' => $v['path']]));
    }*/
});
Breadcrumbs::for('cms.navinode.create', function ($trail, $value) {
    $trail->parent('cms.navinode.index', $value);
    $trail->push('新增');
});
Breadcrumbs::for('cms.navinode.edit', function ($trail, $value) {
    $trail->parent('cms.navinode.index', []);
    $trail->push('編輯');
    // $trail->push($value['title']);
});

// 自訂頁面管理
Breadcrumbs::for('cms.custom-pages.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('自訂頁面管理', route('cms.custom-pages.index'));
});
Breadcrumbs::for('cms.custom-pages.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.custom-pages.index');
    $trail->push('新增');
});
Breadcrumbs::for('cms.custom-pages.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.custom-pages.index');
    $trail->push('編輯');
});

// 活動-四季鮮果
Breadcrumbs::for('cms.act-fruits.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('活動-四季鮮果', route('cms.act-fruits.index'));
});
Breadcrumbs::for('cms.act-fruits.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.act-fruits.index');
    $trail->push('新增水果');
});
Breadcrumbs::for('cms.act-fruits.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.act-fruits.index');
    $trail->push('編輯水果');
});
Breadcrumbs::for('cms.act-fruits.season', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.act-fruits.index');
    $trail->push('水果分類設定');
});


/**
 * 帳務管理
 **/

// 收款作業
Breadcrumbs::for('cms.collection_received.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('收款作業', route('cms.collection_received.index'));
});
Breadcrumbs::for('cms.collection_received.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.collection_received.index', route('cms.collection_received.index'));
    $trail->push('編輯收款單');
});

// 付款作業
Breadcrumbs::for('cms.collection_payment.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('付款作業', route('cms.collection_payment.index'));
});
Breadcrumbs::for('cms.collection_payment.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.collection_payment.index', route('cms.collection_payment.index'));
    $trail->push('編輯付款單');
});
Breadcrumbs::for('cms.collection_payment.edit_note', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.collection_payment.index', route('cms.collection_payment.index'));
    $trail->push('編輯付款項目備註');
});
Breadcrumbs::for('cms.collection_payment.payable_list', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.collection_payment.index', route('cms.collection_payment.index'));
    $trail->push('付款記錄');
});
Breadcrumbs::for('cms.collection_payment.claim', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.collection_payment.index', route('cms.collection_payment.index'));
    $trail->push('合併付款');
});
Breadcrumbs::for('cms.collection_payment.po-edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.collection_payment.index', route('cms.collection_payment.index'));
    $trail->push('合併付款', route('cms.collection_payment.claim'));
    $trail->push('新增付款單');
});
Breadcrumbs::for('cms.collection_payment.po-show', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.collection_payment.index', route('cms.collection_payment.index'));
    $trail->push('合併付款', route('cms.collection_payment.claim'));
    $trail->push('付款單');
});
Breadcrumbs::for('cms.collection_payment.refund-po-edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.collection_payment.index', route('cms.collection_payment.index'));
    $trail->push('新增退出付款單');
});
Breadcrumbs::for('cms.collection_payment.refund-po-show', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.collection_payment.index', route('cms.collection_payment.index'));
    $trail->push('退出付款單');
});

// 請款單作業
Breadcrumbs::for('cms.request.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('請款單作業', route('cms.request.index'));
});
Breadcrumbs::for('cms.request.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.request.index');
    $trail->push('新增請款單');
});
Breadcrumbs::for('cms.request.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.request.index');
    $trail->push('編輯請款單');
});
Breadcrumbs::for('cms.request.show', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.request.index');
    $trail->push('請款單');
});
Breadcrumbs::for('cms.request.ro-edit', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.request.index');
    $trail->push('請款單', route('cms.request.show', ['id' => $value['id']]));
    $trail->push('新增收款單');
});
Breadcrumbs::for('cms.request.ro-receipt', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.request.index');
    $trail->push('請款單', route('cms.request.show', ['id' => $value['id']]));
    $trail->push('收款單');
});
Breadcrumbs::for('cms.request.ro-review', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.request.index');
    $trail->push('請款單', route('cms.request.show', ['id' => $value['id']]));
    $trail->push('收款單', route('cms.request.ro-receipt', ['id' => $value['id']]));
    $trail->push('入款審核');
});
Breadcrumbs::for('cms.request.ro-taxation', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.request.index');
    $trail->push('請款單', route('cms.request.show', ['id' => $value['id']]));
    $trail->push('收款單', route('cms.request.ro-receipt', ['id' => $value['id']]));
    $trail->push('修改摘要/稅別');
});

// 代墊單作業
Breadcrumbs::for('cms.stitute.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('代墊單作業', route('cms.stitute.index'));
});
Breadcrumbs::for('cms.stitute.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.stitute.index');
    $trail->push('新增代墊單');
});
Breadcrumbs::for('cms.stitute.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.stitute.index');
    $trail->push('編輯代墊單');
});
Breadcrumbs::for('cms.stitute.show', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.stitute.index');
    $trail->push('代墊單');
});
Breadcrumbs::for('cms.stitute.po-edit', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.stitute.index');
    $trail->push('代墊單', route('cms.stitute.show', ['id' => $value['id']]));
    $trail->push('新增付款單');
});
Breadcrumbs::for('cms.stitute.po-show', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.stitute.index');
    $trail->push('代墊單', route('cms.stitute.show', ['id' => $value['id']]));
    $trail->push('付款單');
});

// 應收帳款
Breadcrumbs::for('cms.account_received.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('應收帳款', route('cms.account_received.index'));
});
Breadcrumbs::for('cms.account_received.claim', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.account_received.index');
    $trail->push('應收帳款入款');
});
Breadcrumbs::for('cms.account_received.ro-edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.account_received.index');
    $trail->push('新增收款單');
});
Breadcrumbs::for('cms.account_received.ro-receipt', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.account_received.index');
    $trail->push('收款單');
});
Breadcrumbs::for('cms.account_received.ro-review', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.account_received.index');
    $trail->push('收款單', route('cms.account_received.ro-receipt', ['id' => $value['id']]));
    $trail->push('入款審核');
});
Breadcrumbs::for('cms.account_received.ro-taxation', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.account_received.index');
    $trail->push('收款單', route('cms.account_received.ro-receipt', ['id' => $value['id']]));
    $trail->push('修改摘要/稅別');
});

// 應付帳款
Breadcrumbs::for('cms.accounts_payable.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('應付帳款', route('cms.accounts_payable.index'));
});
Breadcrumbs::for('cms.accounts_payable.claim', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.accounts_payable.index');
    $trail->push('應付帳款付款');
});
Breadcrumbs::for('cms.accounts_payable.po-edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.accounts_payable.index');
    $trail->push('新增付款單');
});
Breadcrumbs::for('cms.accounts_payable.po-show', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.accounts_payable.index');
    $trail->push('付款單');
});

// 退款作業
Breadcrumbs::for('cms.refund.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('退款作業', route('cms.refund.index'));
});

// 轉帳傳票
Breadcrumbs::for('cms.transfer_voucher.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('轉帳傳票', route('cms.transfer_voucher.index'));
});
Breadcrumbs::for('cms.transfer_voucher.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.transfer_voucher.index');
    $trail->push('新增轉帳傳票');
});
Breadcrumbs::for('cms.transfer_voucher.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.transfer_voucher.index');
    $trail->push('編輯轉帳傳票');
});
Breadcrumbs::for('cms.transfer_voucher.show', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.transfer_voucher.index');
    $trail->push('轉帳傳票');
});

// 應收票據
Breadcrumbs::for('cms.note_receivable.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('應收票據', route('cms.note_receivable.index'));
});
Breadcrumbs::for('cms.note_receivable.record', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.note_receivable.index');
    $trail->push('應收票據明細');
});
Breadcrumbs::for('cms.note_receivable.ask', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.note_receivable.index');
    $trail->push($value['title']);
});
Breadcrumbs::for('cms.note_receivable.detail', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.note_receivable.index');
    $trail->push($value['title']);
});

// 應付票據
Breadcrumbs::for('cms.note_payable.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('應付票據', route('cms.note_payable.index'));
});
Breadcrumbs::for('cms.note_payable.record', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.note_payable.index');
    $trail->push('應付票據明細');
});
Breadcrumbs::for('cms.note_payable.ask', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.note_payable.index');
    $trail->push($value['title']);
});
Breadcrumbs::for('cms.note_payable.detail', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.note_payable.index');
    $trail->push($value['title']);
});
Breadcrumbs::for('cms.note_payable.checkbook', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.note_payable.index');
    $trail->push('列印支票本');
});

// 信用卡作業管理
Breadcrumbs::for('cms.credit_manager.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('信用卡作業管理', route('cms.credit_manager.index'));
});
Breadcrumbs::for('cms.credit_manager.record', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.credit_manager.index');
    $trail->push('信用卡刷卡記錄');
});
Breadcrumbs::for('cms.credit_manager.record-edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.credit_manager.index');
    $trail->push('編輯信用卡刷卡記錄');
});
Breadcrumbs::for('cms.credit_manager.ask', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.credit_manager.index');
    $trail->push('信用卡整批請款');
});
Breadcrumbs::for('cms.credit_manager.claim', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.credit_manager.index');
    $trail->push('信用卡整批入款');
});
Breadcrumbs::for('cms.credit_manager.income-detail', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.credit_manager.index');
    $trail->push('信用卡入款明細');
});
Breadcrumbs::for('cms.credit_card.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.credit_manager.index');
    $trail->push('信用卡列表', route('cms.credit_card.index'));
});
Breadcrumbs::for('cms.credit_card.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.credit_card.index');
    $trail->push('新增');
});
Breadcrumbs::for('cms.credit_card.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.credit_card.index');
    $trail->push('編輯');
});
// 銀行列表
Breadcrumbs::for('cms.credit_bank.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.credit_manager.index');
    $trail->push('銀行列表', route('cms.credit_bank.index'));
});
Breadcrumbs::for('cms.credit_bank.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.credit_bank.index');
    $trail->push('新增');
});
Breadcrumbs::for('cms.credit_bank.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.credit_bank.index');
    $trail->push('編輯');
});
// 信用卡銀行請款比例
Breadcrumbs::for('cms.credit_percent.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.credit_manager.index');
    $trail->push('請款比例', route('cms.credit_percent.index'));
});
Breadcrumbs::for('cms.credit_percent.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.credit_percent.index');
    $trail->push('新增');
});
Breadcrumbs::for('cms.credit_percent.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.credit_percent.index');
    $trail->push('編輯');
});

// 匯款紀錄
Breadcrumbs::for('cms.remittance_record.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push(' 匯款紀錄', route('cms.remittance_record.index', []));
});
Breadcrumbs::for('cms.remittance_record.detail', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.remittance_record.index');
    $trail->push('匯款明細');
});

// 電子發票作業管理 - 查詢
Breadcrumbs::for('cms.order_invoice_manager.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('電子發票作業管理', route('cms.order_invoice_manager.index'));
});
// 電子發票作業管理 - 月報表
Breadcrumbs::for('cms.order_invoice_manager.month', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('電子發票作業管理', route('cms.order_invoice_manager.month'));
});

// 日結作業
Breadcrumbs::for('cms.day_end.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('日結作業', route('cms.day_end.index'));
});
Breadcrumbs::for('cms.day_end.detail', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.day_end.index');
    $trail->push('日結清單');
});
Breadcrumbs::for('cms.day_end.balance', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.day_end.index');
    $trail->push('現金/銀行存款餘額', route('cms.day_end.balance'));
});
Breadcrumbs::for('cms.day_end.balance_check', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.day_end.balance');
    $trail->push('餘額明細');
});
Breadcrumbs::for('cms.day_end.show', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.day_end.index');
    $trail->push('日結明細');
});

// 分類帳
Breadcrumbs::for('cms.ledger.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('分類帳', route('cms.ledger.index'));
});
Breadcrumbs::for('cms.ledger.detail', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.ledger.index');
    $trail->push('分類帳明細', route('cms.ledger.detail'));
});

// 分潤報表
Breadcrumbs::for('cms.order-bonus.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('分潤報表', route('cms.order-bonus.index'));
});
Breadcrumbs::for('cms.order-bonus.detail', function (BreadcrumbTrail $trail, $data) {
    $trail->parent('cms.order-bonus.index');
    $trail->push($data['month']->title, route('cms.order-bonus.detail', ['id' => $data['month']->id]));
});
Breadcrumbs::for('cms.order-bonus.person-detail', function (BreadcrumbTrail $trail, $data) {
    $trail->parent('cms.order-bonus.detail', $data);
    $trail->push($data['title']);
});
Breadcrumbs::for('cms.order-bonus.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.order-bonus.index');
    $trail->push('新增');
});


/**
 * 總帳會計
 **/

// 會計科目
Breadcrumbs::for('cms.general_ledger.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('會計科目', route('cms.general_ledger.index'));
});
Breadcrumbs::for('cms.general_ledger.create', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.general_ledger.index');
    $trail->push('新增', route('cms.general_ledger.create', ['type' => $value['type']]));
});
Breadcrumbs::for('cms.general_ledger.edit', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.general_ledger.index');
    $trail->push('編輯', route('cms.general_ledger.edit', ['id' => $value['id'], 'type' => $value['type']]));
});
Breadcrumbs::for('cms.general_ledger.show', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.general_ledger.index');
    $trail->push($value['grade_name'], route('cms.general_ledger.show', ['id' => $value['id'], 'type' => $value['type']]));
});


/**
 * 會計設定
 **/

//會計分類
Breadcrumbs::for('cms.first_grade.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('會計分類', route('cms.first_grade.index'));
});
Breadcrumbs::for('cms.first_grade.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('會計分類', route('cms.first_grade.create'));
});

//科目類別
Breadcrumbs::for('cms.income_statement.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('科目類別', route('cms.income_statement.index'));
});
Breadcrumbs::for('cms.income_statement.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('科目類別', route('cms.income_statement.create'));
});

//付款單科目
Breadcrumbs::for('cms.payable_default.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('付款單科目', route('cms.payable_default.index'));
});
Breadcrumbs::for('cms.payable_default.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.payable_default.index');
    $trail->push('編輯付款單科目', route('cms.payable_default.edit'));
});

//收款單科目
Breadcrumbs::for('cms.received_default.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('收款單科目', route('cms.received_default.index'));
});
Breadcrumbs::for('cms.received_default.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.received_default.index');
    $trail->push('編輯收款單科目', route('cms.received_default.edit'));
});


/**
 * 行政管理
 **/

//公佈欄
Breadcrumbs::for('cms.bulletin_board.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('公佈欄列表', Route('cms.bulletin_board.index'));
});
Breadcrumbs::for('cms.bulletin_board.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.bulletin_board.index');
    $trail->push('新增公吿');
});
Breadcrumbs::for('cms.bulletin_board.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.bulletin_board.index');
    $trail->push('編輯公吿');
});
Breadcrumbs::for('cms.bulletin_board.show', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.bulletin_board.index');
    $trail->push('主旨：' . $value);
});

// 申議書
Breadcrumbs::for('cms.petition.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('申議書列表', Route('cms.petition.index'));
});
Breadcrumbs::for('cms.petition.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.petition.index');
    $trail->push('新增申議書');
});
Breadcrumbs::for('cms.petition.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.petition.index');
    $trail->push('編輯申議書');
});
Breadcrumbs::for('cms.petition.show', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.petition.index');
    $trail->push('主旨：' . $value);
});
Breadcrumbs::for('cms.petition.audit-list', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.petition.index');
    $trail->push('待審核申議書列表');
});
Breadcrumbs::for('cms.petition.audit-confirm', function (BreadcrumbTrail $trail, $value) {
    $trail->push('申議書', Route('cms.petition.audit-list'));
    $trail->push('主旨：' . $value);
});

// 支出憑單
Breadcrumbs::for('cms.expenditure.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('支出憑單列表', Route('cms.expenditure.index'));
});
Breadcrumbs::for('cms.expenditure.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.expenditure.index');
    $trail->push('新增支出憑單');
});
Breadcrumbs::for('cms.expenditure.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.expenditure.index');
    $trail->push('編輯支出憑單');
});
Breadcrumbs::for('cms.expenditure.show', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.expenditure.index');
    $trail->push('主旨：' . $value);
});
Breadcrumbs::for('cms.expenditure.audit-list', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.expenditure.index');
    $trail->push('待審核支出憑單列表');
});
Breadcrumbs::for('cms.expenditure.audit-confirm', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.expenditure.audit-list');
    $trail->push('主旨：' . $value);
});


/**
 * 帳號管理
 **/

// 員工帳號管理
Breadcrumbs::for('cms.user.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('員工帳號管理', route('cms.user.index'));
});
Breadcrumbs::for('cms.user.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.user.index');
    $trail->push('新增');
});
Breadcrumbs::for('cms.user.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.user.index');
    $trail->push('編輯');
});
Breadcrumbs::for('cms.user.salechannel', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.user.index');
    $trail->push('通路權限');
});

// 消費者帳號管理
Breadcrumbs::for('cms.customer.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('消費者帳號管理', route('cms.customer.index'));
});
Breadcrumbs::for('cms.customer.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.customer.index');
    $trail->push('新增');
});
Breadcrumbs::for('cms.customer.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.customer.index');
    $trail->push('編輯');
});
Breadcrumbs::for('cms.customer.order', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.customer.index');
    $trail->push('會員專區');
    $trail->push('我的訂單');
});
Breadcrumbs::for('cms.customer.coupon', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.customer.index');
    $trail->push('會員專區');
    $trail->push('我的優惠卷');
});
Breadcrumbs::for('cms.customer.dividend', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.customer.index');
    $trail->push('會員專區');
    $trail->push('我的鴻利');
});
Breadcrumbs::for('cms.customer.address', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.customer.index');
    $trail->push('會員專區');
    $trail->push('收件地址管理');
});
Breadcrumbs::for('cms.customer.bonus', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.customer.index');
    $trail->push('會員專區');
    $trail->push('分潤');
});

// 分潤審核管理
Breadcrumbs::for('cms.customer-profit.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('分潤審核管理', route('cms.customer-profit.index'));
});
Breadcrumbs::for('cms.customer-profit.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.customer-profit.index');
    $trail->push('新增');
});
Breadcrumbs::for('cms.customer-profit.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.customer-profit.index');
    $trail->push('編輯');
});

// 角色管理
Breadcrumbs::for('cms.role.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('角色管理', route('cms.role.index'));
});
Breadcrumbs::for('cms.role.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.role.index');
    $trail->push('新增');
});
Breadcrumbs::for('cms.role.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.role.index');
    $trail->push('編輯');
});

// 頁面權限管理
Breadcrumbs::for('cms.permission.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('頁面權限管理', route('cms.permission.index'));
});
Breadcrumbs::for('cms.permission.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.permission.index');
    $trail->push('新增');
});
Breadcrumbs::for('cms.permission.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.permission.index');
    $trail->push('編輯');
});
Breadcrumbs::for('cms.permission.child', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.permission.index');
    $trail->push('權限設定 [' . $value->title . ']', route('cms.permission.child', ['id' => $value->id]));
});
Breadcrumbs::for('cms.permission.child-create', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.permission.child', $value);
    $trail->push('新增');
});
Breadcrumbs::for('cms.permission.child-edit', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.permission.child', $value);
    $trail->push('編輯');
});

// 消費者紅利點數
Breadcrumbs::for('cms.user-dividend.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('消費者紅利點數');
});


/**
 * 共用頁
 **/

Breadcrumbs::for('cms.logistic.changeLogisticStatus', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.' . $value['parent'] . '.index');
    $trail->push('#' . $value['sn'] . ' 配送狀態');
});
Breadcrumbs::for('cms.delivery.create', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.' . $value['parent'] . '.index');
    $trail->push('#' . $value['sn'] . ' 出貨審核');
});
Breadcrumbs::for('cms.logistic.create', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.' . $value['parent'] . '.index');
    $trail->push('#' . $value['sn'] . ' 實際物流設定');
});
Breadcrumbs::for('cms.delivery.back_list', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.' . $value['parent'] . '.index');
    $trail->push('#' . $value['sn'] . ' 退貨');
});
Breadcrumbs::for('cms.delivery.back_create', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.' . $value['parent'] . '.index');
    $trail->push('#' . $value['sn'] . ' 新增退貨');
});
Breadcrumbs::for('cms.delivery.back_edit', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.' . $value['parent'] . '.index');
    $trail->push('#' . $value['sn'] . ' 編輯退貨');
});
Breadcrumbs::for('cms.delivery.back_inbound', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.' . $value['parent'] . '.index');
    $trail->push('#' . $value['sn'] . ' 退貨入庫審核');
});
Breadcrumbs::for('cms.delivery.back_detail', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.' . $value['parent'] . '.index');
    $trail->push('#' . $value['sn'] . ' 銷貨退回明細');
});
Breadcrumbs::for('cms.delivery.out_stock_detail', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.' . $value['parent'] . '.index');
    $trail->push('#' . $value['sn'] . ' 缺貨退回明細');
});
Breadcrumbs::for('cms.delivery.roe-po', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    if($value['behavior'] == 'return'){
        $trail->push('#' . $value['sn'] . ' 銷貨退回明細', $value['po_source_link']);
        $trail->push('退貨付款單');

    } else if($value['behavior'] == 'out'){
        $trail->push('#' . $value['sn'] . ' 缺貨退回明細', $value['po_source_link']);
        $trail->push('缺貨付款單');

    } else if($value['behavior'] == 'exchange'){

    }
});
Breadcrumbs::for('cms.delivery.roe-po-create', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    if($value['behavior'] == 'return'){
        $trail->push('#' . $value['sn'] . ' 銷貨退回明細', $value['po_source_link']);
        $trail->push('退貨付款單', $value['po_link']);
        $trail->push('新增付款');

    } else if($value['behavior'] == 'out'){
        $trail->push('#' . $value['sn'] . ' 缺貨退回明細', $value['po_source_link']);
        $trail->push('缺貨付款單', $value['po_link']);
        $trail->push('新增付款');

    } else if($value['behavior'] == 'exchange'){

    }
});

// 單據綁定
Breadcrumbs::for('cms.reverse-bind-page', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('單據綁定');
});

// 修改相關單號
Breadcrumbs::for('cms.ref_expenditure_petition.edit', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.dashboard');
    $trail->push('編輯相關單號');
});

// Google數位行銷
Breadcrumbs::for('cms.google_marketing.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('Google數位行銷', route('cms.google_marketing.index'));
});

// 新增Google Ads 轉換追蹤
Breadcrumbs::for('cms.google_marketing.create_ads_events', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('Google數位行銷', route('cms.google_marketing.index'));
    $trail->push('新增Google Ads 轉換追蹤 ');
});

// 採購庫存比較0917
Breadcrumbs::for('cms.inbound_fix0917_import.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('採購庫存比較0917', route('cms.inbound_fix0917_import.index'));
});