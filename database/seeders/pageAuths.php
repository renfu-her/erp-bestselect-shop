<?php

return [
    [
        "unit" => "商品及庫存",
        "permissions" => [
            [
                "cms.product.index",
                "瀏覽",
            ],
            [
                "cms.product.create",
                "新增",
            ],
            [
                "cms.product.edit",
                "修改",            ],
            [
                "cms.product.delete",
                "刪除",
            ],
        ],
    ],
    [
        "unit" => "倉庫設定",
        "permissions" => [
            [
                "cms.depot.index",
                "瀏覽",
            ],
            [
                "cms.depot.create",
                "新增",
            ],
            [
                "cms.depot.edit",
                "修改",            ],
            [
                "cms.depot.delete",
                "刪除",
            ],
        ],
    ],
    [
        "unit" => "廠商管理",
        "permissions" => [
            [
                "cms.supplier.index",
                "瀏覽",
            ],
            [
                "cms.supplier.create",
                "新增",
            ],
            [
                "cms.supplier.edit",
                "修改",
            ],
            [
                "cms.supplier.delete",
                "刪除",
            ],
        ],
    ],
    [
        "unit" => "銷售通路管理",
        "permissions" => [
            [
                "cms.sale_channel.index",
                "瀏覽",
            ],
            [
                "cms.sale_channel.create",
                "新增",
            ],
            [
                "cms.sale_channel.edit",
                "修改",
            ],
            [
                "cms.sale_channel.delete",
                "刪除",
            ],
        ],
    ],
    [
        "unit" => "商品類別",
        "permissions" => [
            [
                "cms.category.index",
                "瀏覽",
            ],
            [
                "cms.category.create",
                "新增",
            ],
            [
                "cms.category.edit",
                "修改",
            ],
            [
                "cms.category.delete",
                "刪除",
            ],
        ],
    ],
    [
        "unit" => "員工帳號管理",
        "permissions" => [
            [
                "cms.user.index",
                "瀏覽",
            ],
            [
                "cms.user.create",
                "新增",
            ],
            [
                "cms.user.edit",
                "編輯",
            ],
            [
                "cms.user.delete",
                "刪除",
            ],
            [
                "cms.user.permit",
                "編輯各單元權限",
            ],
        ],
    ],
    [
        "unit" => "角色管理",
        "permissions" => [
            [
                "cms.role.index",
                "瀏覽",
            ],
            [
                "cms.role.create",
                "新增",
            ],
            [
                "cms.role.edit",
                "編輯",
            ],
            [
                "cms.role.delete",
                "刪除",
            ],
        ],
    ],
    [
        "unit" => "頁面權限管理",
        "permissions" => [
            [
                "cms.permission.index",
                "瀏覽",
            ],
            [
                "cms.permission.create",
                "新增",
            ],
            [
                "cms.permission.edit",
                "名稱修改",
            ],
            [
                "cms.permission.child",
                "權限設定",
            ],
            [
                "cms.permission.delete",
                "刪除",
            ],
        ],
    ],
];
