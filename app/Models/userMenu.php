<?php
return
[
    [
        "title" => "進銷存退",
        "icon" => "bi-basket",
        "menu_id" => "1",
        "child" => [
            [
                "title" => "商品及庫存",
                "controller_name" => "ProductCtrl",
                "route_name" => "cms.product.index",
            ],
        ],
    ],
    [
        "title" => "設定",
        "icon" => "bi-sliders",
        "menu_id" => "2",
        "child" => [
            [
                "title" => "商品類別",
                "controller_name" => "CategoryController",
                "route_name" => "cms.category.index",
            ],
            [
                "title" => "廠商管理",
                "controller_name" => "SupplierCtrl",
                "route_name" => "cms.supplier.index",
            ],
            [
                "title" => "新增銷售通路",
                "controller_name" => "SaleChannelCtrl",
                "route_name" => "cms.sale_channel.index",
            ],
        ],
    ],
    [
        "title" => "帳號管理",
        "icon" => "bi-person-circle",
        "menu_id" => "3",
        "child" => [
            [
                "title" => "員工帳號管理",
                "controller_name" => "UserCtrl",
                "route_name" => "cms.user.index",
            ],
            [
                "title" => "角色管理",
                "controller_name" => "RoleCtrl",
                "route_name" => "cms.role.index",
            ],
            [
                "title" => "頁面權限管理",
                "controller_name" => "PermissionCtrl",
                "route_name" => "cms.permission.index",
            ],
        ],
    ],
];
