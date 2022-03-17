<?php
return
    [
    [
        "title" => "進銷存退",
        "icon" => "bi-basket",
        "menu_id" => "1",
        "child" => [
            [
                "title" => "商品管理",
                "controller_name" => "ProductCtrl",
                "route_name" => "cms.product.index",
            ],
            [
                "title" => "庫存管理",
                "controller_name" => "StockCtrl",
                "route_name" => "cms.stock.index",
            ],
            [
                "title" => "採購單管理",
                "controller_name" => "PurchaseCtrl",
                "route_name" => "cms.purchase.index",
            ],
            [
                "title" => "組合包組裝",
                "controller_name" => "ComboPurchaseCtrl",
                "route_name" => "cms.combo-purchase.index",
            ],
            [
                "title" => "訂單管理",
                "controller_name" => "OrderCtrl",
                "route_name" => "cms.order.index",
            ],
            [
                "title" => "出貨管理",
                "controller_name" => "DeliveryCtrl",
                "route_name" => "cms.delivery.index",
            ],
        ],
    ],
    [
        "title" => "行銷設定",
        "icon" => "bi-shop",
        "menu_id" => "8",
        "child" => [
            [
                "title" => "優惠劵 / 序號",
                "controller_name" => "PromoCtrl",
                "route_name" => "cms.promo.index",
            ],
            [
                "title" => "現折優惠",
                "controller_name" => "DiscountCtrl",
                "route_name" => "cms.discount.index",
            ],
        ],
    ],
    [
        "title" => "設定",
        "icon" => "bi-sliders",
        "menu_id" => "2",
        "child" => [
            [
                "title" => "款式設定",
                "controller_name" => "SpecCtrl",
                "route_name" => "cms.spec.index",
            ],
            [
                "title" => "商品類別",
                "controller_name" => "CategoryController",
                "route_name" => "cms.category.index",
            ],
            [
                "title" => "倉庫管理",
                "controller_name" => "DepotCtrl",
                "route_name" => "cms.depot.index",
            ],
            [
                "title" => "廠商管理",
                "controller_name" => "SupplierCtrl",
                "route_name" => "cms.supplier.index",
            ],
            [
                "title" => "銷售通路管理",
                "controller_name" => "SaleChannelCtrl",
                "route_name" => "cms.sale_channel.index",
            ],
            [
                "title" => "物流運費管理",
                "controller_name" => "ShipmentCtrl",
                "route_name" => "cms.shipment.index",
            ],
        ],
    ],
    [
        "title" => "官網設定",
        "icon" => "bi-house-door",
        "menu_id" => "3",
        "child" => [
            [
                "title" => "首頁設定",
                "controller_name" => "NavbarCtrl",
                "route_name" => "cms.homepage.navbar.index",
            ],
            [
                "title" => "商品群組",
                "controller_name" => "CollectionCtrl",
                "route_name" => "cms.collection.index",
            ],
            [
                "title" => "選單列表設定",
                "controller_name" => "NaviNodeCtrl",
                "route_name" => "cms.navinode.index",
            ],
        ],
    ],
//    [
//        "title" => "帳務管理",
//        "icon" => "bi-cash-coin",
//        "menu_id" => "4",
//        "child" => [
//            [
//                "title" => "付款作業",
//                "controller_name" => "AccountPayableCtrl",
//                "route_name" => "cms.ap.index",
//            ],
//            [
//                "title" => "收款作業",
//                "controller_name" => "AccountReceivedCtrl",
//                "route_name" => "cms.ar.index",
//            ],
//        ],
//    ],
    [
        "title" => "總帳會計",
        "icon" => "bi-journal-text",
        "menu_id" => "5",
        "child" => [
            [
                "title" => "會計科目",
                "controller_name" => "GeneralLedgerCtrl",
                "route_name" => "cms.general_ledger.index",
            ],
        ],
    ],
    [
        "title" => "會計設定",
        "icon" => "bi-bar-chart-steps",
        "menu_id" => "6",
        "child" => [
            [
                "title" => "會計分類",
                "controller_name" => "BalanceSheetCtrl",
                "route_name" => "cms.first_grade.index",
            ],
            [
                "title" => "科目類別",
                "controller_name" => "IncomeStatementCtrl",
                "route_name" => "cms.income_statement.index",
            ],
            [
                "title" => "收支科目",
                "controller_name" => "IncomeExpenditureCtrl",
                "route_name" => "cms.income_expenditure.index",
            ],
        ],
    ],
    [
        "title" => "帳號管理",
        "icon" => "bi-person-circle",
        "menu_id" => "7",
        "child" => [
            [
                "title" => "員工帳號管理",
                "controller_name" => "UserCtrl",
                "route_name" => "cms.user.index",
            ],
            [
                "title" => "消費者帳號管理",
                "controller_name" => "CustomerCtrl",
                "route_name" => "cms.customer.index",
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
