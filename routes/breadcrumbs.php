<?php
use Diglactic\Breadcrumbs\Breadcrumbs;

// This import is also not required, and you could replace `BreadcrumbTrail $trail`
//  with `$trail`. This is nice for IDE type checking and completion.
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Home
Breadcrumbs::for('cms.dashboard', function (BreadcrumbTrail $trail) {
    $trail->push('Home', route('cms.dashboard'));
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
// 編輯 - 網頁-運送方式
Breadcrumbs::for('cms.product.edit-web-logis', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.index');
    $trail->push('[' . $value->title . '] 網頁-運送方式');
});
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
    $trail->push('[單號：' . $value['sn'] . '] 採購單資訊', route('cms.purchase.edit', ['id' => $value['id']]));
});
// 編輯 - 採購單資訊 - 新增訂金付款單
Breadcrumbs::for('cms.purchase.pay-deposit', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.purchase.edit', $value);
    $trail->push('新增訂金付款單');
});
// 編輯 - 採購單資訊 - 新增尾款付款單
Breadcrumbs::for('cms.purchase.pay-final', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.purchase.edit', $value);
    $trail->push('新增尾款付款單');
});
//顯示訂單付款單
Breadcrumbs::for('cms.purchase.pay-order', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.purchase.edit', $value);
    $trail->push('付款單');
});
Breadcrumbs::for('cms.purchase.view-pay-order', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.purchase.edit', $value);
    $trail->push('付款單');
});
// 編輯 - 變更紀錄
Breadcrumbs::for('cms.purchase.log', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.purchase.index');
    $trail->push('[單號：' . $value . '] 變更紀錄');
});
// 編輯 - 入庫審核
Breadcrumbs::for('cms.purchase.inbound', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.purchase.index');
    $trail->push('[單號：' . $value . '] 入庫審核');
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
    $trail->push('[單號：' . $value . '] 訂單明細');
});
Breadcrumbs::for('cms.order.create', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.order.index');
    $trail->push('新增訂單');
});

// 出貨管理
Breadcrumbs::for('cms.delivery.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('出貨管理', route('cms.delivery.index'));
});

/**
 * 行銷設定
 **/

// 現折優惠
Breadcrumbs::for('cms.discount.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('現折優惠', route('cms.discount.index'));
});
// 優惠券
Breadcrumbs::for('cms.promo-coupon.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('優惠券', route('cms.promo-coupon.index'));
});
// 優惠代碼
Breadcrumbs::for('cms.promo-code.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('優惠代碼', route('cms.promo-code.index'));
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
    $trail->parent('cms.navinode.index',$value);
    $trail->push('新增');
});
Breadcrumbs::for('cms.navinode.edit', function ($trail, $value) {
    $trail->parent('cms.navinode.index',[]);
    $trail->push('編輯');
   // $trail->push($value['title']);
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

//收支科目
Breadcrumbs::for('cms.income_expenditure.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('收支科目', route('cms.income_expenditure.index'));
});
Breadcrumbs::for('cms.income_expenditure.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('收支科目', route('cms.income_expenditure.edit'));
});
Breadcrumbs::for('cms.income_expenditure.update', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('收支科目', route('cms.income_expenditure.update'));
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
    $trail->push('消費者帳號管理', route('cms.user.index'));
});
Breadcrumbs::for('cms.customer.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.customer.index');
    $trail->push('新增');
});
Breadcrumbs::for('cms.customer.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.customer.index');
    $trail->push('編輯');
});

