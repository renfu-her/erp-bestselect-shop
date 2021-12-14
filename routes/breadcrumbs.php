<?php
use Diglactic\Breadcrumbs\Breadcrumbs;

// This import is also not required, and you could replace `BreadcrumbTrail $trail`
//  with `$trail`. This is nice for IDE type checking and completion.
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Home
Breadcrumbs::for('cms.dashboard', function (BreadcrumbTrail $trail) {
    $trail->push('Home', route('cms.dashboard'));
});

// 廠商管理
Breadcrumbs::for('supplier.index', function ($trail) {
    $trail->parent('cms.dashboard');
    $trail->push('廠商管理', route('supplier.index'));
});
Breadcrumbs::for('supplier.create', function ($trail) {
    $trail->parent('supplier.index');
    $trail->push('新增');
});
Breadcrumbs::for('supplier.edit', function ($trail) {
    $trail->parent('supplier.index');
    $trail->push('編輯');
});
