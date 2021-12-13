<?php
use Diglactic\Breadcrumbs\Breadcrumbs;

// This import is also not required, and you could replace `BreadcrumbTrail $trail`
//  with `$trail`. This is nice for IDE type checking and completion.
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Home
Breadcrumbs::for('cms.dashboard', function (BreadcrumbTrail $trail) {
    $trail->push('Home', route('cms.dashboard'));
});

// 新增商品
Breadcrumbs::for('cms.product.create', function (BreadcrumbTrail $trail) {
    $trail->parent('cms.dashboard');
    $trail->push('新增商品');
});
