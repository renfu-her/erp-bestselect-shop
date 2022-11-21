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
                "修改"],
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
            [
                "cms.product.clone",
                "複製來源",
            ],
        ],
    ],
    [
        "unit" => "庫存管理",
        "permissions" => [
            [
                "cms.stock.index",
                "瀏覽",
            ],
            [
                "cms.stock.export-detail",
                "匯出庫存明細",
            ],
            [
                "cms.stock.export-check",
                "匯出盤點明細",
            ],
        ],
    ],
    [
        "unit" => "採購單庫存匯入",
        "permissions" => [
            [
                "cms.inbound_import.index",
                "瀏覽",
            ],
            [
                "cms.inbound_import.edit",
                "編輯",
            ],
        ],
    ],
    [
        "unit" => "採購單庫存比較0917匯入",
        "permissions" => [
            [
                "cms.inbound_fix0917_import.index",
                "瀏覽",
            ],
            [
                "cms.inbound_fix0917_import.edit",
                "編輯",
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
                "修改"],
            [
                "cms.depot.delete",
                "刪除",
            ],
            /*
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
         */
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
        "unit" => "通知信管理",
        "permissions" => [
            [
                "cms.mail_set.index",
                "瀏覽",
            ],
            [
                "cms.mail_set.edit",
                "修改",
            ],
        ],
    ],
    [
        "unit" => "首頁設定",
        "permissions" => [
            [
                "cms.homepage.banner.index",
                "瀏覽",
            ],
            [
                "cms.homepage.edit",
                "編輯",
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
        "unit" => "商品群組",
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
            /*
        [
        "cms.collection.publish",
        "公開商品群組",
        ],
         */
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
            [
                "cms.purchase.inbound",
                "入庫",
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
            [
                "cms.collection_received.edit",
                "編輯",
            ],
            [
                "cms.collection_received.delete",
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
            [
                "cms.collection_payment.edit",
                "編輯",
            ],
            [
                "cms.collection_payment.delete",
                "刪除",
            ],
            [
                "cms.collection_payment.logistic-po-create",
                "新增物流付款單",
            ],
        ],
    ],
    [
        "unit" => "請款單作業",
        "permissions" => [
            [
                "cms.request.index",
                "瀏覽",
            ],
            [
                "cms.request.show",
                "詳細資訊",
            ],
            [
                "cms.request.create",
                "新增",
            ],
            [
                "cms.request.edit",
                "編輯",
            ],
            [
                "cms.request.delete",
                "刪除",
            ],
        ],
    ],
    [
        "unit" => "代墊單作業",
        "permissions" => [
            [
                "cms.stitute.index",
                "瀏覽",
            ],
            [
                "cms.stitute.show",
                "詳細資訊",
            ],
            [
                "cms.stitute.create",
                "新增",
            ],
            [
                "cms.stitute.edit",
                "編輯",
            ],
            [
                "cms.stitute.delete",
                "刪除",
            ],
        ],
    ],
    [
        "unit" => "應收帳款",
        "permissions" => [
            [
                "cms.account_received.index",
                "瀏覽",
            ],
            [
                "cms.account_received.edit",
                "編輯",
            ],
        ],
    ],
    [
        "unit" => "應付帳款",
        "permissions" => [
            [
                "cms.accounts_payable.index",
                "瀏覽",
            ],
            [
                "cms.accounts_payable.edit",
                "編輯",
            ],
        ],
    ],
    [
        "unit" => "退款作業",
        "permissions" => [
            [
                "cms.refund.index",
                "瀏覽",
            ],
        ],
    ],
    [
        "unit" => "轉帳傳票",
        "permissions" => [
            [
                "cms.transfer_voucher.index",
                "瀏覽",
            ],
            [
                "cms.transfer_voucher.show",
                "詳細資訊",
            ],
            [
                "cms.transfer_voucher.create",
                "新增",
            ],
            [
                "cms.transfer_voucher.edit",
                "編輯",
            ],
            [
                "cms.transfer_voucher.delete",
                "刪除",
            ],
        ],
    ],
    [
        "unit" => "應收票據",
        "permissions" => [
            [
                "cms.note_receivable.index",
                "瀏覽",
            ],
            [
                "cms.note_receivable.show",
                "詳細資訊",
            ],
            [
                "cms.note_receivable.edit",
                "編輯",
            ],
        ],
    ],
    [
        "unit" => "應付票據",
        "permissions" => [
            [
                "cms.note_payable.index",
                "瀏覽",
            ],
            [
                "cms.note_payable.show",
                "詳細資訊",
            ],
            [
                "cms.note_payable.edit",
                "編輯",
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
        "unit" => "匯款紀錄",
        "permissions" => [
            [
                "cms.remittance_record.index",
                "瀏覽",
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
        "unit" => "日結作業",
        "permissions" => [
            [
                "cms.day_end.index",
                "瀏覽",
            ],
        ],
    ],
    [
        "unit" => "分類帳",
        "permissions" => [
            [
                "cms.ledger.index",
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
                "cms.general_ledger.show",
                "詳細資訊",
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
        ],
    ],
    [
        "unit" => "付款單科目",
        "permissions" => [
            [
                "cms.payable_default.index",
                "瀏覽",
            ],
            [
                "cms.payable_default.edit",
                "編輯",
            ],
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
                "cms.received_default.edit",
                "編輯",
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
            [
                "cms.consignment.inbound",
                "入庫",
            ],
        ],
    ],
    [
        "unit" => "寄倉訂購列表",
        "permissions" => [
            [
                "cms.consignment-order.index",
                "瀏覽",
            ],
            [
                "cms.consignment-order.create",
                "新增",
            ],
            [
                "cms.consignment-order.edit",
                "修改",
            ],
            [
                "cms.consignment-order.delete",
                "刪除",
            ],
        ],
    ],
    [
        "unit" => "寄倉庫存列表",
        "permissions" => [
            [
                "cms.consignment-stock.index",
                "瀏覽",
            ],
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
            [
                'cms.user.salechannel',
                '通路權限',
            ],
        ],
    ],
    [
        "unit" => "消費者帳號管理",
        "permissions" => [
            [
                "cms.customer.index",
                "瀏覽",
            ],
            [
                "cms.customer.create",
                "新增",
            ],
            [
                "cms.customer.edit",
                "編輯",
            ],
            [
                "cms.customer.address",
                "會員專區",
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
                "cms.delivery.edit",
                "編輯",
            ],
        ],
    ],
    [
        "unit" => "出貨商品查詢",
        "permissions" => [
            [
                "cms.delivery_product.index",
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
    [
        "unit" => "訂單管理",
        "permissions" => [
            [
                "cms.order.index",
                "瀏覽",
            ],
            [
                "cms.order.cancel_order",
                "取消訂單",
            ],
            [
                "cms.order.split_order",
                "分割訂單",
            ],
            [
                "cms.order.change_bonus_owner",
                "變更推薦業務員",
            ],
            [
                "cms.order.detail",
                "訂單明細",
            ],
            [
                "cms.order.manual-send-bonus",
                "手動發送紅利",
            ],
            [
                "cms.order.bonus-gross",
                "獎金毛利",
            ],
            [
                "cms.order.edit-item",
                "編輯訂單",
            ],
        ],
    ],

    [
        "unit" => "組合包組裝",
        "permissions" => [
            [
                "cms.combo-purchase.index",
                "瀏覽",
            ],
            [
                "cms.combo-purchase.edit",
                "編輯",
            ],
        ],
    ],
    [
        "unit" => "優惠劵/代碼",
        "permissions" => [
            [
                "cms.promo.index",
                "瀏覽",
            ],
            [
                "cms.promo.create",
                "新增",
            ],
            [
                "cms.promo.edit",
                "編輯",
            ],
            [
                "cms.promo.delete",
                "刪除",
            ],
        ],
    ],
    [
        "unit" => "全館優惠",
        "permissions" => [
            [
                "cms.discount.index",
                "瀏覽",
            ],
            [
                "cms.discount.create",
                "新增",
            ],
            [
                "cms.discount.edit",
                "編輯",
            ],
            [
                "cms.discount.delete",
                "刪除",
            ],
        ],
    ],
    [
        "unit" => "團購主公司管理",
        "permissions" => [
            [
                "cms.groupby-company.index",
                "瀏覽",
            ],
            [
                "cms.groupby-company.create",
                "新增",
            ],
            [
                "cms.groupby-company.edit",
                "修改",
            ],
        ],
    ],
    [
        "unit" => "選單列表設定",
        "permissions" => [
            [
                "cms.navinode.index",
                "瀏覽",
            ],
            [
                "cms.navinode.create",
                "新增",
            ],
            [
                "cms.navinode.edit",
                "修改",
            ],
            [
                "cms.navinode.delete",
                "刪除",
            ],
        ],
    ],
    [
        "unit" => "自訂頁面管理",
        "permissions" => [
            [
                "cms.custom-pages.index",
                "瀏覽",
            ],
            [
                "cms.custom-pages.create",
                "新增",
            ],
            [
                "cms.custom-pages.edit",
                "修改",
            ],
            [
                "cms.custom-pages.delete",
                "刪除",
            ],
        ],
    ],
    [
        "unit" => "分潤報表",
        "permissions" => [
            [
                "cms.order-bonus.index",
                "瀏覽",
            ],
            [
                "cms.order-bonus.create",
                "新增",
            ],
            [
                "cms.order-bonus.delete",
                "刪除",
            ],
            [
                "cms.order-bonus.detail",
                "詳細",
            ],
            [
                "cms.order-bonus.export-csv",
                "輸出csv",
            ],

        ],
    ],
    [
        "unit" => "分潤審核管理",
        "permissions" => [
            [
                "cms.customer-profit.index",
                "瀏覽",
            ],
            [
                "cms.customer-profit.create",
                "新增",
            ],
            [
                "cms.customer-profit.edit",
                "編輯",
            ],
        ],
    ],
    [
        "unit" => "業績報表",
        "permissions" => [
            [
                "cms.user-performance-report.index",
                "瀏覽",
            ],
            [
                "cms.user-performance-report.renew",
                "重新統計",
            ],
        ],
    ],
    [
        "unit" => "業績報表",
        "permissions" => [
            [
                "cms.user-performance-report.index",
                "瀏覽",
            ],
            [
                "cms.user-performance-report.renew",
                "重新統計",
            ],
        ],
    ],
    [
        "unit" => "採購營收報表",
        "permissions" => [
            [
                "cms.product-manager-report.index",
                "瀏覽",
            ],
            [
                "cms.product-manager-report.renew",
                "重新統計",
            ],
        ],
    ],
    [
        "unit" => "營業額目標",
        "permissions" => [
            [
                "cms.vob-performance-report.index",
                "瀏覽",
            ],
            [
                "cms.vob-performance-report.renew",
                "重新統計",
            ],
        ],
    ],
    [
        "unit" => "通關優惠",
        "permissions" => [
            [
                "cms.coupon-event.index",
                "瀏覽",
            ],
            [
                "cms.coupon-event.create",
                "新增",
            ],
            [
                "cms.coupon-event.edit",
                "修改",
            ],
            [
                "cms.coupon-event.delete",
                "刪除",
            ],
        ],
    ],
    [
        "unit" => "組織架構",
        "permissions" => [
            [
                "cms.organize.index",
                "瀏覽",
            ],
            [
                "cms.organize.edit",
                "修改",
            ],

        ],
    ],
    [
        "unit" => "申議書",
        "permissions" => [
            [
                "cms.petition.index",
                "瀏覽",
            ],
            [
                "cms.petition.admin",
                "管理權限",
            ],

        ],
    ],
    [
        "unit" => "支出憑單",
        "permissions" => [
            [
                "cms.expenditure.index",
                "瀏覽",
            ],
            [
                "cms.expenditure.admin",
                "管理權限",
            ],

        ],
    ],


];
