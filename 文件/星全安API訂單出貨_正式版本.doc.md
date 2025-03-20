

版本：2.2  
**版本資訊**

| 版本 | 修改內容 | 修改人 | 日期 |
| :---: | ----- | :---: | :---: |
| V1.0 | 新增交易資料傳送API規格 | Otis | 2019/06/10 |
| V2.0 | 異動串接URL | Sam | 2023/04/26 |
| V2.1 | 欄位顯示異動 | Sam | 2023/06/01 |
| V2.2 | MOMO傳入欄位新增goodsCode 與 ProdName | Sam | 2023/07/05 |

**交易資料傳送API規格**

**說明：**

* 以Form Post方式傳送  
* 編碼方式為UTF-8

**傳入參數格式說明：**  
交易主機位置－：https://b2b.youbon.com/api/orders.php

| 項目 | 欄位 | 說明 |
| ----- | ----- | ----- |
| 傳入參數 | as\_xmldata | 傳入XML電文(URLEncoded) |
| 傳入參數 | as\_hash | 驗證碼 md5(as\_xmldata \+ code ) |
| 傳入參數 | departid | 部門代碼(URLEncoded) |

Ps. code為交易密碼（外掛參數設定）

**xmldata格式：**

* R/O： R: 必填，O: 非必填

| structure | 說明 | 長度 | 型態 | R/O | 備註 |
| ----- | ----- | ----- | ----- | ----- | ----- |
| saleorder |  |  | String |  |  |
| │├ departid | 網購平臺編號 | 8 | String | R | 在正航中為部門編號 |
| │├ userid | 操作人員編號 | 10 | String | R | 在外掛中為操作人員編號 |
| │├ username | 操作人員姓名 | 10 | String | R | 在外掛中為操作人員姓名 |
| │├ saletype | 交易類型 | 10 | String | R | 以此欄位判斷是B2B2C還是B2C |
| │├ custbillno | 訂單編號 | 20 | String | R |  |
| │├ fullname | 客戶姓名 | 80 | String | R |  |
| │├ telephone | 客戶手機 | 16 | String | R | 根據手機判斷客戶是否存在，不存在則新增客戶資料到正航 |
| │├ email | 電子信箱 | 50 | String | R | 若有則自動寄送票券 |
| │├ paymenttype | 付款方式 | 6 | String | R | B2C： 001：現金  002：刷卡  003：其他  004：匯款  005：支票  B2B2C： 003：其他 |
| └ listnumbertype | 接收資訊方式 |  | String | R | 1:預設票券編號 2:網址URL 3:信託碼&效期(取號) 4:券樣圖檔網址 |
| └ items |  |  | String |  |  |
|   └ item |  |  | String |  |  |
|     ├ productnumber | 產品編號 | 12 | String | R | 如：TCV001-03，信託時產品編號+一組數量 1、若「產品編號」(prodnumber) 不為空時，以「產品編號」為準，由ERP尋找符合的「票卷編號」進行出貨。 2、若「產品編號」(prodnumber) 為空時，出貨時，以「票券編號」(prodid)進行出貨  |
|     ├ goodsCode |  |  |  | R | 通路品項編號(MOMO專用) |
|     ├ ProdName |  |  |  | R | 通路品項名稱(MOMO專用) |
|     ├ quantity | 數量 | 8 | String | R | 實際借出數量為該數量\*一組數量 |
|     ├ price | 售價 | 10 | String | R | 為單一票卷之價格 |

**範例：**  
**訂單內容：**  
\<saleorder\>\<departid\>5001\</departid\>\<userid\>5001\</userid\>\<username\>5001\</username\>\<saletype\>B2B2C\</saletype\>\<custbillno\>order001\</custbillno\>\<fullname\>星全安旅行社\</fullname\>\<telephone\>15123456789\</telephone\>\<email\>15123456789@163.com\</email\>\<paymenttype\>003\</paymenttype\>\<listnumbertype\>1\</listnumbertype\>\<items\>\<item\>\<productnumber\>TCV001-03\</productnumber\>\<quantity\>1\</quantity\>\<price\>99\</price\>\</item\>\<item\>\<productnumber\>TCV002-02\</productnumber\>\<quantity\>1\</quantity\>\<price\>99\</price\>\</item\>\</items\>\</saleorder\>

**as\_xmldata=URLEncode(訂單內容，utf-8)**  
%3Csaleorder%3E%3Cdepartid%3E5001%3C%2Fdepartid%3E%3Cuserid%3E5001%3C%2Fuserid%3E%3Cusername%3E5001%3C%2Fusername%3E%3Csaletype%3EB2B2C%3C%2Fsaletype%3E%3Ccustbillno%3Eorder001%3C%2Fcustbillno%3E%3Cfullname%3E%E6%98%9F%E5%85%A8%E5%AE%89%E6%97%85%E8%A1%8C%E7%A4%BE%3C%2Ffullname%3E%3Ctelephone%3E15123456789%3C%2Ftelephone%3E%3Cemail%3E15123456789%40163.com%3C%2Femail%3E%3Cpaymenttype%3E003%3C%2Fpaymenttype%3E%3Clistnumbertype%3E1%3C%2Flistnumbertype%3E%3Citems%3E%3Citem%3E%3Cproductnumber%3ETCV001-03%3C%2Fproductnumber%3E%3Cquantity%3E1%3C%2Fquantity%3E%3Cprice%3E99%3C%2Fprice%3E%3C%2Fitem%3E%3Citem%3E%3Cproductnumber%3ETCV002-02%3C%2Fproductnumber%3E%3Cquantity%3E1%3C%2Fquantity%3E%3Cprice%3E99%3C%2Fprice%3E%3C%2Fitem%3E%3C%2Fitems%3E%3C%2Fsaleorder%3E

（若交易碼為5001star）：  
**as\_hash**: 05134300d617a97d7c55fc9f1861ad0c**回覆參數格式說明：**

| 項目 | 欄位 | 說明 |
| ----- | ----- | ----- |
| 回覆參數 | xmldata | 回覆XML電文(URLEncoded) |

R/O： R: 必填，O: 非必填

| structure | 說明 | 長度 | 型態 | R/O | 備註 |
| ----- | ----- | ----- | ----- | ----- | ----- |
| responseorder |  |  | String |  |  |
| │├ departid | 部門編號 | 5 | String | R | 同傳入資料 |
| │├ userid | 操作人員編號 | 10 | String | R | 同傳入資料 |
| │├ username | 操作人員姓名 | 10 | String | R | 同傳入資料 |
| │├ saletype | 交易類型 | 10 | String | R | 以此欄位判斷是B2B2C還是B2C |
| │├ custbillno | 訂單編號 | 20 | String | R | 同傳入資料 |
| │├ billno | 外掛借出單號 批次借出單號 | 14 | String | R | 交易不成功則為空 |
| │├ borrowno | 正航借出單號 | 14 | String | R | 交易不成功則為空 |
| │├ billdate | 借出日期 | 8 | String | R | 交易不成功則為0 |
| │├ paymenttype | 付款方式 | 6 | String | R | 同傳入資料 |
| │├ statcode | 狀態回覆碼 | 5 | String | R | 交易資料處理的結果代碼(0000代表成功)，其它代碼由宏誠編碼，狀態說明( statdesc ) 必須對應正確的描述 |
| │├ statdesc | 狀態說明 | 60 | String | R | 交易資料處理的結果說明： 0000-成功 9900-訂單重複發送會回傳成功 0001-出貨有狀況，請洽我方人員 0002-此筆訂單已經出貨過，請勿重複送資料 0003-查無此訂單 0004-付款方式錯誤 0005-部門編號錯誤 0006-售出金額有誤，請洽我方人員 0007-額度不足無法出貨，請補充額度後再送資料出貨 0008-此訂單已退貨 0009-交易類型錯誤 0010-網購平臺編號錯誤 0011-操作人員編號錯誤 0012-操作人員姓名錯誤 0013-訂單編號空白 0014-有商品編號查詢不到資料 0015-E-mail空白 9999-訂單異常 |
| │├ weburl | 票券連結網址 | 100 | String | O |  |
| └ items |  |  |  |  | 成功才回傳 |
|   └ item |  |  | String |  |  |
|     ├ productnumber | 產品編號 | 9 | String | R | 同傳入資料，當listnumbertype為1,2,3,4時，此欄位才會出現 |
|     ├ prodid | 票券編號 | 40 | String | R | 實際借出票券編號，當listnumbertype為1,2,4時，此欄位才會出現 |
|     ├ batchid | 批號 | 20 | String | R | 實際借出票號，當listnumbertype為1,2,4時，此欄位才會出現 |
|     ├ ordernumber | 票券號碼 | 30 | String | R | 實際借出完整票券號碼，當listnumbertype為1,2,3,4時，此欄位才會出現 |
|     ├ price | 售價 | 10 | String | R | 當listnumbertype為1,2時，此欄位才會出現 |
|     ├ ticketstardate | 票券使用起始日期 | 10 | String | R | 當listnumbertype為3時，此欄位才會出現 |
|     ├ ticketctdate | 票券使用期限 | 10 | String | R | 當listnumbertype為3時，此欄位才會出現 |
|     ├ ticketoverdate | 票券使用結束日期 | 10 | String | R | 當listnumbertype為3時，此欄位才會出現(已廢棄預設帶0) |
|     ├ images | 券樣圖檔連結路徑 | 120 | String | R | 當listnumbertype為4時，此欄位才會出現 |

**範例：**  
處理成功後回傳：  
當listnumbertype 為1時:  
xmldata=  
\<responseorder\>  
     \<departid\> 5001\</departid\>  
\<userid\>5001\</userid\>  
\<username\>5001\</username\>  
\<saletype\>B2B2C\</saletype\>  
\<custbillno\>order001\</custbillno\>  
     \<billno\>20140411-00001\</billno\>  
\<borrowno\>1404116270001\</borrowno\>  
\<billdate\>20140411\</billdate\>  
\<paymenttype\>003\</paymenttype \>  
	\<statcode\>0000\</statcode\>  
     \<statdesc\>成功\</statdesc\>  
\<weburl\>https://www.youbon.com/kkdayticket/orderview.php?os=n4teefd4ua1szgrirecgz4witc6767e9\</weburl\>  
     \<items\>  
          \<item\>  
			\<productnumber\> TCV001\</productnumber\>  
               \<prodid\> AAOOO243241611303\</prodid\>  
               \<batchid\>000001\</batchid\>  
			\<ordernumer\> AAOOO243241611303000001\</ordernumber\>  
               \<price\>99\</price\>  
          \</item\>  
\<item\>  
	\<productnumber\>TCV001\</productnumber\>  
               \<prodid\> AAOOO243241611303\</prodid\>  
               \<batchid\>000002\</batchid\>  
\<ordernumer\> AAOOO243241611303000002\</ordernumber\>  
               \<price\>99\</price\>  
          \</item\>  
\<item\>  
	\<productnumber\>TCV001\</productnumber\>  
               \<prodid\> AAOOO243241611303\</prodid\>  
               \<batchid\>000003\</batchid\>  
\<ordernumer\> AAOOO243241611303000003\</ordernumber\>  
               \<price\>99\</price\>  
          \</item\>  
     \</items\>  
\</responseorder\>

當listnumbertype 為2時:  
xmldata=  
\<responseorder\>  
     \<departid\> 5001\</departid\>  
\<userid\>5001\</userid\>  
\<username\>5001\</username\>  
\<saletype\>B2B2C\</saletype\>  
\<custbillno\>order001\</custbillno\>  
     \<billno\>20140411-00001\</billno\>  
\<borrowno\>1404116270001\</borrowno\>  
\<billdate\>20140411\</billdate\>  
\< paymenttype \>003\</paymenttype \>  
	\<statcode\>0000\</statcode\>  
     \<statdesc\>成功\</statdesc\>  
     \<weburl\>https://www.youbon.com/kkdayticket/orderview.php?os=n4teefd4ua1szgrirecgz4witc6767e9\</weburl\>  
     \<items\>  
          \<item\>  
			\<productnumber\> TCV001\</productnumber\>  
               \<prodid\> AAOOO243241611303\</prodid\>  
               \<batchid\>000001\</batchid\>  
			\<ordernumer\> AAOOO243241611303000001\</ordernumber\>  
               \<price\>99\</price\>  
          \</item\>  
\<item\>  
	\<productnumber\>TCV001\</productnumber\>  
               \<prodid\> AAOOO243241611303\</prodid\>  
               \<batchid\>000002\</batchid\>  
\<ordernumer\> AAOOO243241611303000002\</ordernumber\>  
               \<price\>99\</price\>  
          \</item\>  
\<item\>  
	\<productnumber\>TCV001\</productnumber\>  
               \<prodid\> AAOOO243241611303\</prodid\>  
               \<batchid\>000003\</batchid\>  
\<ordernumer\> AAOOO243241611303000003\</ordernumber\>  
               \<price\>99\</price\>  
          \</item\>  
     \</items\>  
\</responseorder\>

當listnumbertype 為3時:  
xmldata=  
\<responseorder\>  
     \<departid\> 5001\</departid\>  
\<userid\>5001\</userid\>  
\<username\>5001\</username\>  
\<saletype\>B2B2C\</saletype\>  
\<custbillno\>order001\</custbillno\>  
     \<billno\>20140411-00001\</billno\>  
\<borrowno\>1404116270001\</borrowno\>  
\<billdate\>20140411\</billdate\>  
\< paymenttype \>003\</paymenttype \>  
	\<statcode\>0000\</statcode\>  
     \<statdesc\>成功\</statdesc\>  
	\<weburl\>https://www.youbon.com/kkdayticket/orderview.php?os=n4teefd4ua1szgrirecgz4witc6767e9\</weburl\>  
     \<items\>  
		\<item\>  
\<productnumber\>0001\</productnumber\>  
   			\<trustnumber\>0070000948744471\</trustnumber\>  
			\<ordernumer\> AAOOO243241611303000001\</ordernumber\>  
			\<ticketstardate\>2019/03/31\</ticketstardate\>  
			\<ticketctdate\>50\</ticketctdate\>  
			\<ticketoverdate\>2019/04/20\</ticketoverdate\>  
\</item\>  
          \<item\>  
\<productnumber\>0002\</productnumber\>  
   			\<trustnumber\>0070000948744471\</trustnumber\>  
			\<ordernumer\> AAOOO243241611303000001\</ordernumber\>  
			\<ticketstardate\>2019/03/31\</ticketstardate\>  
			\<ticketctdate\>0\</ticketctdate\>  
			\<ticketoverdate\>2019/04/20\</ticketoverdate\>  
\</item\>  
     \</items\>  
\</responseorder\>

當listnumbertype 為4時:  
xmldata=  
\<responseorder\>  
     \<departid\>5001\</departid\>  
\<userid\>5001\</userid\>  
\<username\>5001\</username\>  
\<saletype\>B2B2C\</saletype\>  
\<custbillno\>order001\</custbillno\>  
     \<billno\>20140411-00001\</billno\>  
\<borrowno\>1404116270001\</borrowno\>  
\<billdate\>20140411\</billdate\>  
\< paymenttype \>003\</paymenttype \>  
	\<statcode\>0000\</statcode\>  
     \<statdesc\>成功\</statdesc\>  
	\<weburl\>https://www.youbon.com/kkdayticket/orderview.php?os=n4teefd4ua1szgrirecgz4witc6767e9\</weburl\>  
     \<items\>  
          \<item\>  
			\<productnumber\> TCV001\</productnumber\>  
               \<prodid\> AOEEO051048401801\</prodid\>  
               \<batchid\>000170\</batchid\>  
			\<ordernumer\> AOEEO051048401801000170\</ordernumber\>  
               \<images\>https://www.youbon.com/kkdayticket/3v32glexoh8b2o7xoajxcbaicd7cpbgs/AOEEO051048401801000170.jpg\</images\>  
          \</item\>  
\<item\>  
	\<productnumber\>TCV001\</productnumber\>  
               \<prodid\> AOEEO051048401801\</prodid\>  
               \<batchid\>000171\</batchid\>  
\<ordernumer\> AOEEO051048401801000171\</ordernumber\>  
               \<images\>https://www.youbon.com/kkdayticket/3v32glexoh8b2o7xoajxcbaicd7cpbgs/AOEEO051048401801000171.jpg\</images\>  
          \</item\>  
\<item\>  
	\<productnumber\>TCV002\</productnumber\>  
               \<prodid\> AOEEO051048401801\</prodid\>  
               \<batchid\>000172\</batchid\>  
\<ordernumer\> AOEEO051048401801000172\</ordernumber\>  
               \<images\>https://www.youbon.com/kkdayticket/3v32glexoh8b2o7xoajxcbaicd7cpbgs/AOEEO051048401801000172.jpg\</images\>  
          \</item\>  
     \</items\>  
\</responseorder\>

處理失敗：  
xmldata=  
 \<responseorder\>  
\<departid\> 5001\</departid\>  
\<userid\>5001\</userid\>  
\<username\>5001\</username\>  
\<saletype \>B2B2C\</ saletype \>  
\<custbillno\>order001\</custbillno\>  
\< paymenttype \>003\</paymenttype \>  
	\<statcode\>0001\</statcode\>  
     \<statdesc\>庫存不足\</statdesc\>  
\</responseorder\>