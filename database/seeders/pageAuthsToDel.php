<?php

return [
    [
        "unit" => "出貨管理",
        "permissions" => [
            [
                "cms.delivery.create",
                "新增",
            ],
            [
                "cms.delivery.delete",
                "刪除",
            ],
        ],
    ],
    [
        "unit" => "首頁設定",
        "permissions" => [
            [
                "cms.homepage.index",
                "瀏覽",
            ],
        ],
    ],
    [
        "unit" => "寄倉庫存列表",
        "permissions" => [
            [
                "cms.consignment_stock.index",
                "瀏覽",
            ],
        ],
    ],
    [
        "unit" => "寄倉訂購列表",
        "permissions" => [
            [
                "cms.consignment_order.index",
                "瀏覽",
            ],
            [
                "cms.consignment_order.create",
                "新增",
            ],
            [
                "cms.consignment_order.edit",
                "修改",
            ],
            [
                "cms.consignment_order.delete",
                "刪除",
            ],
        ],
    ],
];
