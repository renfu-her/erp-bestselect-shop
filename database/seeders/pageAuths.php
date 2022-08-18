<?php

return [
    [
        "unit" => "商品列表",
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
            [
                "cms.product.show",
                "ERP商品資訊",
            ],
            [
                "cms.product.edit-stock",
                "庫存管理",
            ],
            [
                "cms.product.edit-price",
                "價格管理",
            ],
            [
                "cms.product.edit-style",
                "規格款式",
            ],
            [
                "cms.product.edit-combo",
                "組合包",
            ],
            [
                "cms.product.edit-web-desc",
                "[網頁]商品介紹",
            ],
            [
                "cms.product.edit-web-spec",
                "[網頁]規格說明",
            ],  
            [
                "cms.product.edit-setting",
                "設定",
            ], 

            
        ],
    ],
    [
        "unit" => "倉庫管理",
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
            [
                "cms.depot.product-index",
                "寄倉選品瀏覽",
            ],
            [
                "cms.depot.product-create",
                "寄倉選品新增",
            ],
            [
                "cms.depot.product-edit",
                "寄倉選品修改",
            ],
            [
                "cms.depot.product-delete",
                "寄倉選品刪除",
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
        "unit" => "物流運費管理",
        "permissions" => [
            [
                "cms.shipment.index",
                "瀏覽",
            ],
            [
                "cms.shipment.create",
                "新增",
            ],
            [
                "cms.shipment.edit",
                "修改",
            ],
            [
                "cms.shipment.delete",
                "刪除",
            ],
        ],
    ],
    [
        "unit" => "款式設定",
        "permissions" => [
            [
                "cms.spec.index",
                "瀏覽",
            ],
            [
                "cms.spec.create",
                "新增",
            ],
//            [
//                "cms.spec.edit",
//                "修改",
//            ],
//            [
//                "cms.spec.delete",
//                "刪除",
//            ],
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
        "unit" => "群組設定",
        "permissions" => [
            [
                "cms.collection.index",
                "瀏覽",
            ],
            [
                "cms.collection.create",
                "新增",
            ],
            [
                "cms.collection.edit",
                "修改",
            ],
            [
                "cms.collection.delete",
                "刪除",
            ],
            [
                "cms.collection.publish",
                "公開商品群組",
            ],
        ],
    ],
    [
        "unit" => "採購單列表",
        "permissions" => [
            [
                "cms.purchase.index",
                "瀏覽",
            ],
            [
                "cms.purchase.create",
                "新增",
            ],
            [
                "cms.purchase.edit",
                "修改",
            ],
            [
                "cms.purchase.delete",
                "刪除",
            ],
        ],
    ],
    [
        "unit" => "付款作業",
        "permissions" => [
            [
                "cms.collection_payment.index",
                "瀏覽",
            ],
        ],
    ],
    [
        "unit" => "收款作業",
        "permissions" => [
            [
                "cms.collection_received.index",
                "瀏覽",
            ],
        ],
    ],
    [
        "unit" => "寄倉訂購收款作業",
        "permissions" => [
            [
                "cms.ar_csnorder.index",
                "瀏覽",
            ],
            [
                "cms.ar_csnorder.create",
                "新增",
            ],
            [
                "cms.ar_csnorder.edit",
                "編輯",
            ],
            [
                "cms.ar_csnorder.delete",
                "刪除",
            ],
        ],
    ],
    [
        "unit" => "信用卡作業管理",
        "permissions" => [
            [
                "cms.credit_manager.index",
                "瀏覽",
            ],
        ],
    ],
    [
        "unit" => "信用卡",
        "permissions" => [
            [
                "cms.credit_card.index",
                "瀏覽",
            ],
            [
                "cms.credit_card.create",
                "新增",
            ],
            [
                "cms.credit_card.edit",
                "編輯",
            ],
            [
                "cms.credit_card.delete",
                "刪除",
            ],
        ],
    ],
    [
        "unit" => "信用卡對接銀行",
        "permissions" => [
            [
                "cms.credit_bank.index",
                "瀏覽",
            ],
            [
                "cms.credit_bank.create",
                "新增",
            ],
            [
                "cms.credit_bank.edit",
                "編輯",
            ],
            [
                "cms.credit_bank.delete",
                "刪除",
            ],
        ],
    ],
    [
        "unit" => "信用卡請款比例",
        "permissions" => [
            [
                "cms.credit_percent.index",
                "瀏覽",
            ],
            [
                "cms.credit_percent.create",
                "新增",
            ],
            [
                "cms.credit_percent.edit",
                "編輯",
            ],
            [
                "cms.credit_percent.delete",
                "刪除",
            ],
        ],
    ],
    [
        "unit" => "電子發票作業管理",
        "permissions" => [
            [
                "cms.order_invoice_manager.index",
                "瀏覽",
            ],
            [
                "cms.order_invoice_manager.export_excel_month",
                "匯出月報表",
            ],
        ],
    ],
    [
        "unit" => "匯款紀錄",
        "permissions" => [
            [
                "cms.remittance_record.index",
                "瀏覽",
            ],
        ],
    ],
    [
        "unit" => "會計科目",
        "permissions" => [
            [
                "cms.general_ledger.index",
                "瀏覽",
            ],
            [
                "cms.general_ledger.create",
                "新增",
            ],
            [
                "cms.general_ledger.edit",
                "編輯",
            ],
            [
                "cms.general_ledger.delete",
                "刪除",
            ],
        ],
    ],
    [
        "unit" => "會計分類",
        "permissions" => [
            [
                "cms.first_grade.index",
                "瀏覽",
            ],
            [
                "cms.first_grade.create",
                "新增",
            ],
//            [
//                "cms.first_grade.edit",
//                "編輯",
//            ],
//            [
//                "cms.first_grade.delete",
//                "刪除",
//            ],
        ],
    ],
    [
        "unit" => "科目類別",
        "permissions" => [
            [
                "cms.income_statement.index",
                "瀏覽",
            ],
            [
                "cms.income_statement.create",
                "新增",
            ],
//            [
//                "cms.income_statement.edit",
//                "編輯",
//            ],
//            [
//                "cms.income_statement.delete",
//                "刪除",
//            ],
        ],
    ],
    [
        "unit" => "收款單科目",
        "permissions" => [
            [
                "cms.received_default.index",
                "瀏覽",
            ],
            [
                "cms.received_default.create",
                "新增",
            ],
            [
                "cms.received_default.edit",
                "編輯",
            ],
            [
                "cms.received_default.delete",
                "刪除",
            ],
        ],
    ],
    [
        "unit" => "寄倉單列表",
        "permissions" => [
            [
                "cms.consignment.index",
                "瀏覽",
            ],
            [
                "cms.consignment.create",
                "新增",
            ],
            [
                "cms.consignment.edit",
                "修改",
            ],
            [
                "cms.consignment.delete",
                "刪除",
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
    [
        "unit" => "寄倉庫存列表",
        "permissions" => [
            [
                "cms.consignment_stock.index",
                "瀏覽",
            ]
        ],
    ],
    [
        "unit" => "佈告欄",
        "permissions" => [
            [
                "cms.bulletin_board.index",
                "瀏覽",
            ],
            [
                "cms.bulletin_board.create",
                "新增",
            ],
            [
                "cms.bulletin_board.edit",
                "編輯",
            ],
            [
                "cms.bulletin_board.delete",
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
    [
        "unit" => "出貨管理",
        "permissions" => [
            [
                "cms.delivery.index",
                "瀏覽",
            ],
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
        "unit" => "Google數位行銷",
        "permissions" => [
            [
                "cms.google_marketing.index",
                "瀏覽",
            ],
        ],
    ],
    [
        "unit" => "物流管理",
        "permissions" => [
            [
                "cms.logistic.create",
                "新增",
            ],
            [
                "cms.logistic.delete",
                "刪除",
            ],
        ],
    ],
];
