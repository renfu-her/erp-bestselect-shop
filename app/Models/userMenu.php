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
            [
                "title" => "新增廠商",
                "controller_name" => "SupplierCtrl",
                "route_name" => "cms.supplier.index",
            ],
        ],
    ],
];
