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
// 編輯 - 商品資訊
Breadcrumbs::for('cms.product.edit', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.index');
    $trail->push('[' . $value->title . '] 編輯');
});
// 編輯 - 規格款式
Breadcrumbs::for('cms.product.edit-style', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.index');
    $trail->push('[' . $value->title . '] 規格款式', route('cms.product.edit-style', $value->id));
});
Breadcrumbs::for('cms.product.edit-spec', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.edit-style', $value);
    $trail->push('編輯規格');
});
// 編輯 - 組合包
Breadcrumbs::for('cms.product.edit-combo', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.index');
    $trail->push('[' . $value->title . '] 組合包款式', route('cms.product.edit-combo', ['id' => $value->id]));
});
Breadcrumbs::for('cms.product.create-combo-prod', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.edit-combo', $value);
    $trail->push('新增組合包款式');
});
Breadcrumbs::for('cms.product.edit-combo-prod', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.edit-combo', $value['product']);
    $trail->push('[' . $value['style']->title . '] 編輯');
});
// 編輯 - 銷售控管
Breadcrumbs::for('cms.product.edit-sale', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.index');
    $trail->push('[' . $value->title . '] 銷售控管', route('cms.product.edit-sale', $value->id));
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
    $trail->push('[' . $value->title . '] 網頁-商品介紹');
});
// 編輯 - 網頁-規格說明
Breadcrumbs::for('cms.product.edit-web-spec', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.index');
    $trail->push('[' . $value->title . '] 網頁-規格說明');
});
// // 編輯 - 網頁-運送方式
// Breadcrumbs::for('cms.product.edit-web-logis', function (BreadcrumbTrail $trail, $value) {
//     $trail->parent('cms.product.index');
//     $trail->push('[' . $value->title . '] 網頁-運送方式');
// });
// 編輯 - 設定
Breadcrumbs::for('cms.product.edit-setting', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.index');
    $trail->push('[' . $value->title . '] 設定');
});

// 庫存管理
Breadcrumbs::for('cms.stock.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('庫存管理', route('cms.stock.index'));
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

// 組合包組裝
Breadcrumbs::for('cms.combo-purchase.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('組合包組裝', route('cms.combo-purchase.index'));
});
Breadcrumbs::for('cms.combo-purchase.edit', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.combo-purchase.index');
    $trail->push('[【' . $value['product']->title . '】' . $value['style']->title . '] 組裝/拆包');
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

// 訂單自取入庫審核
Breadcrumbs::for('cms.order.inbound', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 入庫審核', route('cms.order.inbound', ['subOrderId' => $value['id']]));
});
// 新增收款單
Breadcrumbs::for('cms.collection_received.create', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.order.detail', ['id' => $value['id']]));
    $trail->push('新增收款單');
});
//顯示訂單收款單
Breadcrumbs::for('cms.collection_received.receipt', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.order.detail', ['id' => $value['id']]));
    $trail->push('收款單');
});
// 新增電子發票
Breadcrumbs::for('cms.order.create-invoice', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.order.detail', ['id' => $value['id']]));
    $trail->push('開立電子發票');
});
// 顯示電子發票
Breadcrumbs::for('cms.order.show-invoice', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.order.detail', ['id' => $value['id']]));
    $trail->push('電子發票');
});
//編輯收款單入帳日期
Breadcrumbs::for('cms.collection_received.review', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.order.detail', ['id' => $value['id']]));
    $trail->push('收款單', route('cms.collection_received.receipt', ['id' => $value['id']]));
    $trail->push('入款審核');
});
//編輯收款單稅別/摘要/備註
Breadcrumbs::for('cms.collection_received.taxation', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.order.detail', ['id'=>$value['id']]));
    $trail->push('收款單', route('cms.collection_received.receipt', ['id'=>$value['id']]));
    $trail->push('修改摘要/稅別');
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
// 新增收款單
Breadcrumbs::for('cms.ar_csnorder.create', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.consignment-order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.consignment-order.edit', ['id' => $value['id']]));
    $trail->push('新增收款單');
});
//顯示訂單收款單
Breadcrumbs::for('cms.ar_csnorder.receipt', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.consignment-order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.consignment-order.edit', ['id' => $value['id']]));
    $trail->push('收款單');
});
//編輯收款單入帳日期
Breadcrumbs::for('cms.ar_csnorder.review', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.consignment-order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.consignment-order.edit', ['id' => $value['id']]));
    $trail->push('收款單', route('cms.ar_csnorder.receipt', ['id' => $value['id']]));
    $trail->push('入款審核');
});
//編輯收款單稅別/摘要/備註
Breadcrumbs::for('cms.ar_csnorder.taxation', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.consignment-order.index');
    $trail->push('#' . $value['sn'] . ' 訂單明細', route('cms.consignment-order.edit', ['id'=>$value['id']]));
    $trail->push('收款單', route('cms.ar_csnorder.receipt', ['id'=>$value['id']]));
    $trail->push('修改摘要/稅別');
});

// 出貨管理
Breadcrumbs::for('cms.delivery.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('出貨管理', route('cms.delivery.index'));
});

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
    $trail->push('#' . $value['sn'] . ' 寄倉訂購單資訊', route('cms.consignment-order.edit', ['id' => $value['id']]));
});

//寄倉庫存
Breadcrumbs::for('cms.consignment-stock.stocklist', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('寄倉庫存', route('cms.consignment-stock.stocklist'));
});
// 寄倉庫存明細
Breadcrumbs::for('cms.consignment-stock.stock_detail_log', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.consignment-stock.stocklist');
    $trail->push('#' . $value . ' 明細');
});

// *** 共用頁 *** //
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
Breadcrumbs::for('cms.delivery.back', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.' . $value['parent'] . '.index');
    $trail->push('#' . $value['sn'] . ' 退貨');
});
Breadcrumbs::for('cms.delivery.back_inbound', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.' . $value['parent'] . '.index');
    $trail->push('#' . $value['sn'] . ' 退貨入庫審核');
});
Breadcrumbs::for('cms.delivery.back_detail', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.' . $value['parent'] . '.index');
    $trail->push('#' . $value['sn'] . ' 銷貨退回明細');
});
Breadcrumbs::for('cms.delivery.return-pay-order', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 銷貨退回明細', route('cms.delivery.back_detail', ['event' => $value['event'], 'eventId' => $value['eventId']]));
    $trail->push('退貨付款單');
});
Breadcrumbs::for('cms.delivery.return-pay-create', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('#' . $value['sn'] . ' 銷貨退回明細', route('cms.delivery.back_detail', ['event' => $value['event'], 'eventId' => $value['eventId']]));
    $trail->push('退貨付款單', route('cms.delivery.return-pay-order', ['id' => $value['id']]));
    $trail->push('新增付款');
});
/**
 * 行銷設定
 **/

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
    $trail->push('[' . $value . '] 編輯');
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
    $trail->push('[' . $value . '] 編輯');
});

// Google數位行銷
Breadcrumbs::for('cms.google_marketing.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('Google數位行銷', route('cms.google_marketing.index'));
});
//新增Google Ads 轉換追蹤
Breadcrumbs::for('cms.google_marketing.create_ads_events', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('Google數位行銷', route('cms.google_marketing.index'));
    $trail->push('新增Google Ads 轉換追蹤 ');
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
    $trail->push('[#' . $value . '] 編輯');
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
    $trail->push('[#' . $value . '] 編輯');
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

// 付款作業
Breadcrumbs::for('cms.ap.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('付款作業', route('cms.ap.index'));
});

// 代墊單作業
Breadcrumbs::for('cms.stitute.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('代墊單作業', route('cms.stitute.index'));
});
// 新增代墊單
Breadcrumbs::for('cms.stitute.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.stitute.index');
    $trail->push('新增代墊單');
});
// 代墊單
Breadcrumbs::for('cms.stitute.show', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.stitute.index');
    $trail->push('代墊單');
});
// 代墊單付款(新增付款單)
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

// 收款作業
Breadcrumbs::for('cms.collection_received.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('收款作業', route('cms.collection_received.index'));
});

// 請款單作業
Breadcrumbs::for('cms.request.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('請款單作業', route('cms.request.index'));
});
// 新增請款單
Breadcrumbs::for('cms.request.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.request.index');
    $trail->push('新增請款單');
});
// 請款單
Breadcrumbs::for('cms.request.show', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.request.index');
    $trail->push('請款單');
});
// 請款單入款(新增收款單)
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
// 信用卡
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

// 發票作業管理
Breadcrumbs::for('cms.order_invoice_manager.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('發票查詢', route('cms.order_invoice_manager.index'));
});
// 發票作業管理
Breadcrumbs::for('cms.order_invoice_manager.month', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('發票月報表', route('cms.order_invoice_manager.month'));
});

// 請款銀行
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

// 會計科目
Breadcrumbs::for('cms.general_ledger.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('會計科目', route('cms.general_ledger.index'));
});
Breadcrumbs::for('cms.general_ledger.show-1st', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('會計科目', route('cms.general_ledger.index'));
});
Breadcrumbs::for('cms.general_ledger.show-2nd', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('會計科目', route('cms.general_ledger.index'));
});
Breadcrumbs::for('cms.general_ledger.show-3rd', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('會計科目', route('cms.general_ledger.index'));
});
Breadcrumbs::for('cms.general_ledger.show-4th', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('會計科目', route('cms.general_ledger.index'));
});
Breadcrumbs::for('cms.general_ledger.edit-1st', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('會計科目', route('cms.general_ledger.index'));
});
Breadcrumbs::for('cms.general_ledger.edit-2nd', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('會計科目', route('cms.general_ledger.index'));
});
Breadcrumbs::for('cms.general_ledger.edit-3rd', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('會計科目', route('cms.general_ledger.index'));
});
Breadcrumbs::for('cms.general_ledger.edit-4th', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('會計科目', route('cms.general_ledger.index'));
});
Breadcrumbs::for('cms.general_ledger.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('會計科目', route('cms.general_ledger.index'));
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

Breadcrumbs::for('cms.customer-profit.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.customer-profit.index');
    $trail->push('編輯');
});

// 分潤報表
Breadcrumbs::for('cms.order-bonus.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('分潤報表', route('cms.order-bonus.index'));
});

Breadcrumbs::for('cms.order-bonus.detail', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.order-bonus.index');
    $trail->push('內容');
});

Breadcrumbs::for('cms.order-bonus.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.order-bonus.index');
    $trail->push('新增');
});
