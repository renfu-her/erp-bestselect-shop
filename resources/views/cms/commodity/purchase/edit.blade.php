@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">@if ($method === 'create') 新增@else 編輯@endif 採購單</h2>

    <form method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf

        @if ($method === 'edit')
            <input type='hidden' name='id' value="{{ old('id', $id) }}"/>
        @endif
        @error('id')
        <div class="alert alert-danger mt-3">{{ $message }}</div>
        @enderror

        <div class="card shadow p-4 mb-4">
            <div class="row">
                <div class="col-12 col-sm-6 mb-3 ">
                    <label class="form-label">採購廠商</label>
                    <select name="supplier" id="supplier"
                            class="form-select @error('supplier') is-invalid @enderror"
                            aria-label="採購廠商" required>
                        <option value="" selected disabled>請選擇</option>
                        @foreach ($supplierList as $supplierItem)
                            <option value="{{ $supplierItem->id }}"
                                    @if ($supplierItem->id == old('supplier', $data->supplier_id ?? '')) selected @endif>
                                {{ $supplierItem->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 mb-3 ">
                    <label class="form-label">廠商預計進貨日期</label>
                    <div class="input-group has-validation">
                        <input type="date" id="date" name="scheduled_date" value="{{ old('scheduled_date', $data->scheduled_date  ?? '') }}"
                               class="form-control @error('scheduled_date') is-invalid @enderror" aria-label="廠商預計進貨日期"
                               required/>
                        <button class="btn btn-outline-secondary icon" type="button" id="resetDate"
                                data-bs-toggle="tooltip"
                                title="清空日期"><i class="bi bi-calendar-x"></i></button>
                        <div class="invalid-feedback">
                            @error('date')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow p-4 mb-4">
            <h6>採購清單</h6>
            <div class="table-responsive tableOverBox">
                <table class="table table-hover tableList">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center">刪除</th>
                            <th scope="col">商品名稱</th>
                            <th scope="col">SKU</th>
                            <th scope="col">採購數量</th>
                            <th scope="col">採購價錢</th>
                        </tr>
                    </thead>
                    <tbody class="-appendClone">
                        <tr class="-cloneElem">
                            <th class="text-center">
                                <button type="button" data-id=""
                                    class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </th>
                            <td data-td="name">【喜鴻嚴選】咖啡候機室(10入/盒) </td>
                            <td data-td="sku">AA2590</td>
                            <td>
                                <input type="text" class="form-control form-control-sm" value="" />
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" value="" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="d-grid mt-3">
                <button data-bs-toggle="modal" data-bs-target="#addProduct" type="button" 
                class="btn btn-outline-primary border-dashed" style="font-weight: 500;">
                    <i class="bi bi-plus-circle bold"></i> 加入商品
                </button>
            </div>
        </div>

        <div class="card shadow p-4 mb-4">
            <h6>代墊單</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3 ">
                    <label class="form-label">訂金採購單</label>
                    <input class="form-control" type="text" name="deposit_pay_num" placeholder="請輸入訂金採購單"
                           value="{{ old('deposit_pay_num', $depositPayData->order_num ?? '') }}" aria-label="訂金採購單">
                </div>
                <div class="col-12 col-sm-6 mb-3 ">
                    <label class="form-label">尾款採購單</label>
                    <input class="form-control" type="text" name="final_pay_num" placeholder="請輸入尾款採購單"
                           value="{{ old('final_pay_num', $finalPayData->order_num ?? '') }}" aria-label="尾款採購單">
                </div>
            </div>
        </div>

        <div class="card shadow p-4 mb-4">
            <h6>付款資訊</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3 ">
                    <label class="form-label">匯款銀行</label>
                    <input class="form-control @error('bank_cname') is-invalid @enderror" type="text" name="bank_cname" placeholder="請輸入匯款銀行"
                           value="{{ old('bank_cname', $data->bank_cname ?? '') }}" aria-label="匯款銀行">
                    <div class="invalid-feedback">
                        @error('bank_cname')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
                <div class="col-12 col-sm-6 mb-3 ">
                    <label class="form-label">匯款銀行代碼</label>
                    <input class="form-control @error('bank_code') is-invalid @enderror" type="text" name="bank_code" placeholder="請輸入匯款銀行代碼"
                           value="{{ old('bank_code', $data->bank_code ?? '') }}" aria-label="匯款銀行代碼">
                    <div class="invalid-feedback">
                        @error('bank_code')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3 ">
                    <label class="form-label">匯款戶名</label>
                    <input class="form-control @error('bank_acount') is-invalid @enderror" type="text" name="bank_acount" placeholder="請輸入匯款戶名"
                           value="{{ old('bank_acount', $data->bank_acount ?? '') }}" aria-label="匯款戶名">
                    <div class="invalid-feedback">
                        @error('bank_acount')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
                <div class="col-12 col-sm-6 mb-3 ">
                    <label class="form-label">匯款帳號</label>
                    <input class="form-control @error('bank_numer') is-invalid @enderror" type="text" name="bank_numer" placeholder="請輸入匯款帳號"
                           value="{{ old('bank_numer', $data->bank_numer ?? '') }}" aria-label="匯款帳號">
                    <div class="invalid-feedback">
                        @error('bank_numer')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
                <fieldset class="col-12 mb-3">
                    <legend class="col-form-label p-0 mb-2">付款方式</legend>
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="pay_type" id="pay_type1" value="0"
                                   @if (old('pay_type', $data->pay_type ?? '') == '0') checked @endif required aria-label="付款方式">
                            <label class="form-check-label" for="pay_type1">先付(訂金)</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="pay_type" id="pay_type2" value="1"
                                   @if (old('pay_type', $data->pay_type ?? '') == '1') checked @endif required aria-label="付款方式">
                            <label class="form-check-label" for="pay_type2">先付(一次付清)</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="pay_type" id="pay_type3" value="2"
                                   @if (old('pay_type', $data->pay_type ?? '') == '2') checked @endif required aria-label="付款方式">
                            <label class="form-check-label" for="pay_type3">貨到付款</label>
                        </div>
                    </div>
                </fieldset>
            </div>
            <div class="row">
                <div class="col-12 col-sm-3 mb-3 ">
                    <label class="form-label">訂金金額</label>
                    <input class="form-control" type="text" name="deposit_pay_price" placeholder="請輸入訂金金額"
                           value="{{ old('deposit_pay_price', $depositPayData->price ?? '') }}" aria-label="訂金金額">
                </div>
                <div class="col-12 col-sm-3 mb-3 ">
                    <label class="form-label">訂金付款日期</label>
                    <input type="date" class="form-control @error('deposit_pay_date') is-invalid @enderror"
                           name="deposit_pay_date" placeholder="訂金付款日期"
                           value="{{ old('deposit_pay_price', $depositPayData->pay_date ?? '') }}">
                    @error('deposit_pay_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-sm-3 mb-3 ">
                    <label class="form-label">尾款金額</label>
                    <input class="form-control" type="text" name="final_pay_price" placeholder="請輸入尾款金額"
                           value="{{ old('final_pay_price', $finalPayData->price ?? '') }}" aria-label="尾款金額">
                </div>
                <div class="col-12 col-sm-3 mb-3 ">
                    <label class="form-label">尾款付款日期(尾款日不可小於訂金日)</label>
                    <input type="date" class="form-control @error('final_pay_date') is-invalid @enderror"
                           name="final_pay_date" placeholder="尾款付款日期"
                           value="{{ old('final_pay_date', $finalPayData->pay_date ?? '') }}">
                    @error('final_pay_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3 ">
                    <label class="form-label">運費(無外加運費填0)</label>
                    <input class="form-control" type="text" name="logistic_price" placeholder="請輸入運費金額"
                           value="{{ old('logistic_price', $data->logistic_price ?? '') }}" aria-label="運費">
                </div>
            </div>
        </div>

        <div id="submitDiv">
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
                <a href="{{ Route('cms.purchase.index', [], true) }}" class="btn btn-outline-primary px-4" role="button">返回列表</a>
            </div>
        </div>
    </form>

{{-- 商品清單 --}}
<x-b-modal id="addProduct" cancelBtn="false" size="modal-xl modal-fullscreen-lg-down">
    <x-slot name="title">選取商品加入採購清單</x-slot>
    <x-slot name="body">
        <div class="input-group mb-3">
            <input type="text" class="form-control" placeholder="請輸入名稱或SKU" aria-label="搜尋條件">
            <button class="btn btn-primary" type="button">搜尋商品</button>
        </div>
        <div class="row justify-content-end mb-2">
            <div class="col-auto">
                顯示
                <select class="form-select d-inline-block w-auto" id="dataPerPageElem" aria-label="表格顯示筆數">
                    @foreach (config('global.dataPerPage') as $value)
                        <option value="{{ $value }}">{{ $value }}</option>
                    @endforeach
                </select>
                筆
            </div>
        </div>
        <div class="table-responsive mb-3">
            <table class="table table-hover tableList mb-0">
                <thead>
                    <tr>
                        <th scope="col" class="text-center">選取</th>
                        <th scope="col">商品名稱</th>
                        <th scope="col">款式</th>
                        <th scope="col">SKU</th>
                        <th scope="col">庫存數量</th>
                        <th scope="col">預扣庫存量</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th class="text-center">
                            <input class="form-check-input print-check" type="checkbox"
                                value="" data-td="p_id" aria-label="選取商品">
                        </th>
                        <td data-td="name">【喜鴻嚴選】咖啡候機室(10入/盒) </td>
                        <td data-td="styles">綜合口味</td>
                        <td data-td="sku">AA2590</td>
                        <td data-td="stock">58</td>
                        <td data-td="withholding">20</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col d-flex justify-content-end align-items-center flex-wrap">
            <div class="me-1 -pageSum">共 3 頁（共 135 筆資料）</div>
            {{-- 頁碼 --}}
            <div class="d-flex justify-content-center">
                <nav>
                    <ul class="pagination">
                      <li class="page-item">
                            <button type="button" class="page-link" aria-label="Previous">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                      </li>
                      <li class="page-item"><button type="button" class="page-link">1</button></li>
                      <li class="page-item"><button type="button" class="page-link">2</button></li>
                      <li class="page-item disabled"><span class="page-link">...</span></li>
                      <li class="page-item"><button type="button" class="page-link">5</button></li>
                      <li class="page-item"><button type="button" class="page-link">6</button></li>
                      <li class="page-item"><button type="button" class="page-link">7</button></li>
                      <li class="page-item active"><span class="page-link">8</span></li>
                      <li class="page-item"><button type="button" class="page-link">9</button></li>
                      <li class="page-item"><button type="button" class="page-link">10</button></li>
                      <li class="page-item"><button type="button" class="page-link">11</button></li>
                      <li class="page-item"><button type="button" class="page-link">...</button></li>
                      <li class="page-item"><button type="button" class="page-link">14</button></li>
                      <li class="page-item"><button type="button" class="page-link">15</button></li>
                      <li class="page-item">
                            <button type="button" class="page-link" aria-label="Next">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                      </li>
                    </ul>
                </nav>
            </div>
        </div>
    </x-slot>
    <x-slot name="foot">
        <button type="button" class="btn btn-primary btn-ok">加入採購清單</button>
    </x-slot>
</x-b-modal>
@endsection
@once
    @push('sub-scripts')
        <script>
            let supplierList = @json($supplierList);
            $('#supplier').on('change', function (e) {
                if ("" != $('input[name=bank_cname]').val()
                    || "" != $('input[name=bank_code]').val()
                    || "" != $('input[name=bank_acount]').val()
                    || "" != $('input[name=bank_numer]').val()) {
                    if (confirm('下方已設定匯款資訊 是否根據所選廠商做變更?')) {
                        changeRemittance();
                    }
                } else {
                    changeRemittance();
                }
            });

            //變更匯款資料
            let changeRemittance = function () {
                let supplierID = $("#supplier").val();

                let supplierItem = null;
                for (i = 0; i < supplierList.length; i++) {
                    if (supplierList[i].id == supplierID) {
                        supplierItem = supplierList[i];
                        break;
                    }
                }

                if (null != supplierItem) {
                    $('input[name=bank_cname]').val(supplierItem.bank_cname);
                    $('input[name=bank_code]').val(supplierItem.bank_code);
                    $('input[name=bank_acount]').val(supplierItem.bank_acount);
                    $('input[name=bank_numer]').val(supplierItem.bank_numer);
                }
            };
        </script>
        <script>
            /*** 選取商品 ***/
            let selectedProductId = [];
            let selectedProduct = [];
            // clone 項目
            const $clone = $('.-cloneElem:first-child').clone();
            $('.-cloneElem.d-none').remove();

            getProductList(1);
            // 商品清單 API
            function getProductList(page) {
                initPages(100, 15, page);
                return true;

                let _URL = '';
                let Data = {};
                $.ajax({
                    type: "Post",
                    url: _URL,
                    data: Data,
                    dataType: "json",
                    error: function (err) {
                        console.log(err);
                    },
                    success: function (res) {
                        console.log(res);

                        // foreach
                        createOneProduct();
                        // endforeach
                        initPages();
                    }
                });
            }

            // btn - 加入採購清單
            $('#addProduct .btn-ok').off('click').on('click', function () {
                catchCheckedProduct();
            });

            // 紀錄 checked product
            function catchCheckedProduct() {
                $('#addProduct tbody input[data-td="p_id"]:checked').each(function (index, element) {
                    // element == this
                    const pid = $(element).val();
                    if (selectedProductId.indexOf(pid) >= 0) {
                        selectedProductId.push(pid);
                        selectedProduct.push({
                            id: pid,
                            name: $(element).parent('th').siblings('[data-td="name"]').text(),
                            sku: $(element).parent('th').siblings('[data-td="sku"]').text()
                        });
                    }
                });
            }
            
            // 加入一個商品
            function createOneProduct(p) {
                Clone_bindCloneBtn($clone, function (cloneElem) {
                    cloneElem.find('input').val('');
                    cloneElem.find('.-del').attr('data-id', '');
                    cloneElem.find('td[data-td]').text('');
                    if (p) {
                        cloneElem.find('.-del').attr('data-id', p);
                        cloneElem.find('td[data-td="name"]').text(p);
                        cloneElem.find('td[data-td="sku"]').text(p);
                    }
                });
            }

            // 產生 分頁
            function initPages(totalData, totalPages, currentPage) {
                const Max = 13, // 最多數
                    Buffer = 3, // active 鄰居數
                    Edge = 2,   // 頭尾數
                    Ellipsis = 1,   // ...數
                    Active = 1; // active數
                $('#addProduct .-pageSum').text(`共 ${totalPages} 頁（共 ${totalData} 筆資料）`);
                $('#addProduct .page-item').removeClass('disabled').attr('tabindex', null);

                // 分頁
                $('#addProduct .page-item:not(:first-child, :last-child)').remove();
                for (let index = 1; index <= totalPages; index++) {
                    let $li = $('<li class="page-item"></li>');

                    if (totalPages <= Max || 
                        index <= Edge || index > totalPages - Edge ||
                        Math.abs(currentPage - index) <= Buffer ||
                        (Math.abs(currentPage - index) - Ellipsis == Buffer && 
                        (totalPages - Edge === index || Edge + Ellipsis === index))
                    ) {
                        $li = PageLink_N(index, $li);
                    } else {
                        $li = PageLink_Es(index, $li);
                    }
                    
                    if ($li) $('#addProduct .page-item:last-child').before($li);
                }

                // disabled Previous Next
                $('#addProduct .page-item.active:nth-child(2)').prev('.page-item').addClass('disabled');
                $('#addProduct .page-item.active:nth-last-child(2) + .page-item').addClass('disabled');
                $('#addProduct .page-item.disabled').attr('tabindex', -1);

                // bind event
                $('#addProduct .page-item button.page-link').off('click').on('click', function () {
                    catchCheckedProduct();

                    const page = $(this).data('page');
                    getProductList(page);
                });

                // 產生 數字鈕
                function PageLink_N(index, $li) {
                    let $page_link = '';
                    if (index == currentPage) {
                        $page_link = $(`<span class="page-link">${index}</span>`);
                        $li.addClass('active');
                    } else {
                        $page_link = $(`<button class="page-link" type="button">${index}</button>`);
                        $page_link.data('page', index);
                    }
                    $li.append($page_link);
                    return $li;
                }
                // 產生 省略符號
                function PageLink_Es(index, $li) {
                    if ($('#addProduct .page-item:nth-last-child(2) span').text() === '...') {
                        return false;
                    } else {
                        $li.addClass('disabled');
                        $li.append('<span class="page-link">...</span>');
                        return $li;
                    }
                }
            }
        </script>
    @endpush
@endonce
