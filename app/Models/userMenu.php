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
                "title" => "採購單庫存匯入",
                "controller_name" => "InboundImportCtrl",
                "route_name" => "cms.inbound_import.index",
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
        "title" => "寄倉管理",
        "icon" => "bi-basket",
        "menu_id" => "9",
        "child" => [
            [
                "title" => "寄倉搜尋",
                "controller_name" => "ConsignmentCtrl",
                "route_name" => "cms.consignment.index",
            ],
            [
                "title" => "寄倉訂購",
                "controller_name" => "ConsignmentOrderCtrl",
                "route_name" => "cms.consignment-order.index",
            ],
            [
                "title" => "寄倉庫存",
                "controller_name" => "ConsignmentStockCtrl",
                "route_name" => "cms.consignment-stock.index",
            ],
        ],
    ],
    [
        "title" => "報表",
        "icon" => "bi-bar-chart",
        "menu_id" => "10",
        "child" => [
            [
                "title" => "業績報表",
                "controller_name" => "UserPerformanceReportCtrl",
                "route_name" => "cms.user-performance-report.index",
            ],
            [
                "title" => "商品負責人報表",
                "controller_name" => "ProductManagerReportCtrl",
                "route_name" => "cms.product-manager-report.index",
            ],
        ],
    ],
    [
        "title" => "行銷設定",
        "icon" => "bi-shop",
        "menu_id" => "8",
        "child" => [
            [
                "title" => "優惠劵 / 代碼",
                "controller_name" => "PromoCtrl",
                "route_name" => "cms.promo.index",
            ],
            [
                "title" => "全館優惠",
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
            [
                "title" => "團購主公司管理",
                "controller_name" => "GroupbyCompanyCtrl",
                "route_name" => "cms.groupby-company.index",
            ],
            [
                "title" => "通知信管理",
                "controller_name" => "MailSetCtrl",
                "route_name" => "cms.mail_set.index",
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
                "route_name" => "cms.homepage.banner.index",
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
            [
                "title" => "自訂頁面管理",
                "controller_name" => "CustomPagesCtrl",
                "route_name" => "cms.custom-pages.index",
            ],
        ],
    ],
    [
        "title" => "帳務管理",
        "icon" => "bi-cash-coin",
        "menu_id" => "4",
        "child" => [
            [
                "title" => "收款作業",
                "controller_name" => "CollectionReceivedCtrl",
                "route_name" => "cms.collection_received.index",
            ],
            [
                "title" => "付款作業",
                "controller_name" => "CollectionPaymentCtrl",
                "route_name" => "cms.collection_payment.index",
            ],
            [
                "title" => "請款單作業",
                "controller_name" => "RequestOrderCtrl",
                "route_name" => "cms.request.index",
            ],
            [
                "title" => "代墊單作業",
                "controller_name" => "StituteOrderCtrl",
                "route_name" => "cms.stitute.index",
            ],
            [
                "title" => "應收帳款",
                "controller_name" => "AccountReceivedCtrl",
                "route_name" => "cms.account_received.index",
            ],
            [
                "title" => "應付帳款",
                "controller_name" => "AccountsPayableCtrl",
                "route_name" => "cms.accounts_payable.index",
            ],
            [
                "title" => "退款作業",
                "controller_name" => "RefundCtrl",
                "route_name" => "cms.refund.index",
            ],
            [
                "title" => "轉帳傳票",
                "controller_name" => "TransferVoucherCtrl",
                "route_name" => "cms.transfer_voucher.index",
            ],
            [
                "title" => "應收票據",
                "controller_name" => "NoteReceivableCtrl",
                "route_name" => "cms.note_receivable.index",
            ],
            [
                "title" => "應付票據",
                "controller_name" => "NotePayableCtrl",
                "route_name" => "cms.note_payable.index",
            ],
            [
                "title" => "信用卡作業管理",
                "controller_name" => "CreditManagerCtrl",
                "route_name" => "cms.credit_manager.index",
            ],
            [
                "title" => "匯款紀錄",
                "controller_name" => "RemittanceRecordCtrl",
                "route_name" => "cms.remittance_record.index",
            ],
            [
                "title" => "電子發票作業管理",
                "controller_name" => "OrderInvoiceManagerCtrl",
                "route_name" => "cms.order_invoice_manager.index",
            ],
            [
                "title" => "日結作業",
                "controller_name" => "DayEndCtrl",
                "route_name" => "cms.day_end.index",
            ],
            [
                "title" => "分類帳",
                "controller_name" => "LedgerCtrl",
                "route_name" => "cms.ledger.index",
            ],
            [
                "title" => "分潤報表",
                "controller_name" => "OrderBonusCtrl",
                "route_name" => "cms.order-bonus.index",
            ],
        ],
    ],
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
                "controller_name" => "FirstGradeCtrl",
                "route_name" => "cms.first_grade.index",
            ],
            [
                "title" => "科目類別",
                "controller_name" => "IncomeStatementCtrl",
                "route_name" => "cms.income_statement.index",
            ],
            [
                "title" => "付款單科目",
                "controller_name" => "PayableDefaultCtrl",
                "route_name" => "cms.payable_default.index",
            ],
            [
                "title" => "收款單科目",
                "controller_name" => "ReceivedDefaultCtrl",
                "route_name" => "cms.received_default.index",
            ],
        ],
    ],
    [
        "title" => "行政管理",
        "icon" => "bi-person-workspace",
        "menu_id" => "7",
        "child" => [
            [
                "title" => "佈告欄",
                "controller_name" => "BulletinBoardCtrl",
                "route_name" => "cms.bulletin_board.index",
            ],
        ],
    ],
    [
        "title" => "帳號管理",
        "icon" => "bi-person-circle",
        "menu_id" => "8",
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
                "title" => "分潤審核管理",
                "controller_name" => "CustomerProfitCtrl",
                "route_name" => "cms.customer-profit.index",
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
        [
            "title" => "修正",
            "icon" => "bi-wrench-adjustable-circle",
            "menu_id" => "9",
            "child" => [
                [
                    "title" => "採購單庫存比較0917匯入",
                    "controller_name" => "InboundFix0917ImportCtrl",
                    "route_name" => "cms.inbound_fix0917_import.index",
                ],
            ],
        ],
];
