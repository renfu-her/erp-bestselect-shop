<!-- TOC -->

- [1. API](#1-api)
    - [1.1. 入庫單API](#11-入庫單api)
        - [1.1.1. 取得可入庫單 可出貨列表](#111-取得可入庫單可出貨列表)
        - [1.1.2. 新增對應的入庫商品款式](#112-新增對應的入庫商品款式)
        - [1.1.3. 刪除單筆預計出貨倉資料](#113-刪除單筆預計出貨倉資料)
    - [2.1. 物流單API](#11-物流單api)
        - [2.1.1. 耗材新增對應的入庫商品款式](#211-耗材新增對應的入庫商品款式)

<!-- /TOC -->

# 1 API

## 1.1. 入庫單API

### 1.1.1. 取得可入庫單 可出貨列表

```
POST {host}/api/cms/delivery/get-select-inbound-list
```

| request body | -      |      |
| ------------ | ------ | ---- |
| product_style_id            | string |   商品款式ID  |

| response body  | -      |     |
| -------------- | ------ | --- |
| status         | '0':ok |     |
| msg            | string |     |
| data           | obj    |     |
| data.purchase_id | string | 採購ID    |
| data.product_title| string | 商品名稱    |
| data.style_title| string | 款式名稱    |
| data.style_sku| string | 款式sku    |
| data.inbound_id| int | 入庫ID    |
| data.inbound_sn| string | 入庫SN    |
| data.product_style_id| string | 款式ID    |
| data.depot_id| string | 倉庫ID    |
| data.depot_name| string | 倉庫名稱    |
| data.inbound_user_id| string | 入庫者ID    |
| data.inbound_user_name| string | 入庫者名稱    |
| data.inbound_close_date| string | 結單日期    |
| data.inbound_memo| string | 備註    |
| data.inbound_num| int | 入庫數量    |
| data.sale_num| int | 已賣數量    |
| data.tb_rd_qty| string | 暫扣數量    |
| data.qty| string | 可出庫數量 = 入庫數量-已賣數量-暫扣數量    |
| data.expiry_date| string | 有效日期    |
| data.inbound_date| string | 入庫日期    |
| data.deleted_at| string | 刪除日期    |

### 1.1.2. 新增對應的入庫商品款式

```
POST {host}/api/cms/delivery/store-receive-depot
```

>response回傳 剛剛新增的資料

| request body | -      |      |
| ------------ | ------ | ---- |
| delivery_id            | string |   出貨單ID  |
| item_id            | string |   子訂單商品ID  |
| inbound_id[]           | array:int | 入庫單ID  |
| qty[]           | array:int | 數量  |

| response body  | -      |     |
| -------------- | ------ | --- |
| status         | '0':ok |     |
| msg            | string |     |
| data | obj | 對應出貨列表    |
| data.id | int | 出貨商品ID    |
| data.delivery_id | int | 出貨單ID    |
| data.event_item_id | int | 事件ID (在訂單則為子訂單商品ID)    |
| data.freebies | int | 是否為贈品 0:否 1:是    |
| data.inbound_id | int | 入庫ID    |
| data.inbound_sn | string | 入庫SN    |
| data.depot_id | int | 倉庫ID    |
| data.depot_name | string | 倉庫名稱    |
| data.product_style_id | int | 商品款式ID    |
| data.sku | string | 款式sku    |
| data.product_title | string | 商品名稱-款式名稱    |
| data.qty | int | 出貨數量    |
| data.expiry_date | string | 有效日期    |
| data.audit_date | string | 審核日期    |


### 1.1.3. 刪除單筆預計出貨倉資料

```
GET {host}/api/cms/delivery/del-receive-depot/{收貨倉ID receiveDepotId}
```



## 2.1. 物流單API

### 2.1.1. 耗材新增對應的入庫商品款式

```
POST {host}/api/cms/logistic/store-consum
```

>response回傳 剛剛新增的資料

| request body | -      |      |
| ------------ | ------ | ---- |
| logistic_id            | string |   物流單ID  |
| inbound_id[]           | array:int | 入庫單ID  |
| qty[]           | array:int | 數量  |

| response body  | -      |     |
| -------------- | ------ | --- |
| status         | '0':ok |     |
| msg            | string |     |
| data | obj | 對應出貨列表    |
| data.id | int | 出貨商品ID    |
| data.delivery_id | int | 出貨單ID    |
| data.event_item_id | int | 事件ID (在訂單則為子訂單商品ID)    |
| data.freebies | int | 是否為贈品 0:否 1:是    |
| data.inbound_id | int | 入庫ID    |
| data.inbound_sn | string | 入庫SN    |
| data.depot_id | int | 倉庫ID    |
| data.depot_name | string | 倉庫名稱    |
| data.product_style_id | int | 商品款式ID    |
| data.sku | string | 款式sku    |
| data.product_title | string | 商品名稱-款式名稱    |
| data.qty | int | 出貨數量    |
| data.expiry_date | string | 有效日期    |
| data.audit_date | string | 審核日期    |
