<?php
use Diglactic\Breadcrumbs\Breadcrumbs;

// This import is also not required, and you could replace `BreadcrumbTrail $trail`
//  with `$trail`. This is nice for IDE type checking and completion.
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Home
Breadcrumbs::for('cms.dashboard', function (BreadcrumbTrail $trail) {
    $trail->push('Home', route('cms.dashboard'));
});

// 商品主頁
Breadcrumbs::for('cms.product.index', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('商品主頁');
});
// 新增商品
Breadcrumbs::for('cms.product.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('新增商品');
});
// 編輯 - 商品資訊
Breadcrumbs::for('cms.product.edit', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.index');
    $trail->push('商品資訊');
});
// 編輯 - 規格款式
Breadcrumbs::for('cms.product.edit-style', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.index');
    $trail->push('規格款式');
});
// 編輯 - 銷售控管
Breadcrumbs::for('cms.product.edit-sale', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.index');
    $trail->push('銷售控管');
});
// 編輯 - 網頁-商品介紹
Breadcrumbs::for('cms.product.edit-web-desc', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.index');
    $trail->push('網頁-商品介紹');
});
// 編輯 - 網頁-規格說明
Breadcrumbs::for('cms.product.edit-web-spec', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.index');
    $trail->push('網頁-規格說明');
});
// 編輯 - 網頁-運送方式
Breadcrumbs::for('cms.product.edit-web-logis', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.index');
    $trail->push('網頁-運送方式');
});
// 編輯 - 設定
Breadcrumbs::for('cms.product.edit-setting', function (BreadcrumbTrail $trail, $value) {
    $trail->parent('cms.product.index');
    $trail->push('設定');
});

// 廠商管理
Breadcrumbs::for('cms.supplier.index', function ($trail) {
    $trail->parent('cms.dashboard');
    $trail->push('廠商管理', route('cms.supplier.index'));
});
Breadcrumbs::for('cms.supplier.create', function ($trail) {
    $trail->parent('cms.supplier.index');
    $trail->push('新增');
});
Breadcrumbs::for('cms.supplier.edit', function ($trail) {
    $trail->parent('cms.supplier.index');
    $trail->push('編輯');
});

// 商品類別
Breadcrumbs::for('cms.category.index', function ($trail) {
    $trail->parent('cms.dashboard');
    $trail->push('商品類別', route('cms.category.index'));
});
Breadcrumbs::for('cms.category.create', function ($trail) {
    $trail->parent('cms.category.index');
    $trail->push('新增');
});
Breadcrumbs::for('cms.category.edit', function ($trail) {
    $trail->parent('cms.category.index');
    $trail->push('編輯');
});
