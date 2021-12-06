<!-- TOC -->
- [1. Datapicker 快速選擇鍵](#1-datapicker快速選擇鍵)
  - [1.1. 範例](#11-範例)
  - [1.2. 使用](#12-使用)
    - [1.2.1 日期區間](#121-日期區間)
    - [1.2.2 單一日期](#122-單一日期)
```
- resources\js\dashboard.js   // 前端 JS
```
<!-- /TOC -->

# 1. Datapicker快速選擇鍵
## 1.1. 範例
```
<!-- 日期區間 -->
<input type="date" class="-startDate" />
<input type="date" class="-endDate" />
<button data-daysBefore="n" type="button">前n日</button>
<!-- 單一日期 -->
<input type="date" class="-startDate" />
<button data-prevDay="n" type="button">前n日</button>
<button data-nextDay="n" type="button">下n日</button>
```
## 1.2. 使用
### 1.2.1 日期區間
| TAG                       | 說明          |
| ------------------------- | ------------- |
| input.-startDate          | 起始日期 input |
| input.-endDate            | 結束日期 input |
| button`[data-daysBefore]` | 快速鍵         |

| data-daysBefore="n" | 說明    | -startDate  | -endDate         |
| ------------------- | ------ | ----------- | ----------------- |
| int                 | n 天前 | n 天前       | 今天              |
| year                | 今年   | 今年1/1      | 今年12/31         |
| month               | 本月   | 本月1號      | 本月最後一日       |
| quarter             | 本季   | 本季第一月1號 | 本季第三月最後一日 |
| week                | 本周   | 這周日(台)   | 這周六(台)         |
| day                 | 今日   | 今天         | 今天              |

### 1.2.2 單一日期
| TAG                    | 說明          |
| ---------------------- | ------------- |
| input.-startDate       | 目標日期 input |
| button`[data-prevDay]` | 累減快速鍵     |
| button`[data-nextDay]` | 累加快速鍵     |

|                  | 說明                |
| ---------------- | ------------------- |
| data-prevDay="n" | -startDate日期 -n 天 |
| data-nextDay="n" | -startDate日期 +n 天 |