@extends('layouts.main')
@section('sub-content')
    @if ($method === 'edit')
        <h2 class="mb-3">採購單 {{ $purchaseData->purchase_sn }}</h2>
        <x-b-pch-navi :id="$id"></x-b-pch-navi>
    @else
        <h2 class="mb-3">新增採購單</h2>
    @endif


    <form id="form1" method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf

        @error('id')
        <div class="alert alert-danger mt-3">{{ $message }}</div>
        @enderror

        <div class="card shadow p-4 mb-4">
            <div class="row">
                <div class="col-12 col-sm-6 mb-3 ">
                    <label class="form-label">採購廠商{{$purchaseData->supplier_id ?? ''}} <span class="text-danger">*</span></label>
                    <select id="supplier" @if ($method === 'edit') disabled @endif
                    class="form-select -select2 -single @error('supplier') is-invalid @enderror"
                            aria-label="採購廠商" required>
                        <option value="" selected disabled>請選擇</option>
                        @foreach ($supplierList as $supplierItem)
                            <option value="{{ $supplierItem->id }}"
                                    @if ($supplierItem->id == old('supplier', $purchaseData->supplier_id ?? '')) selected @endif>
                                {{ $supplierItem->name }}@if ($supplierItem->nickname)（{{ $supplierItem->nickname }}） @endif
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="supplier" value="">
                    <div class="invalid-feedback">
                        @error('supplier')
                        {{ $message }}
                        @enderror
                    </div>
                </div>

                <div class="col-12 col-sm-6 mb-3 ">
                    <label class="form-label">廠商預計進貨日期 <span class="text-danger">*</span></label>
                    <div class="input-group has-validation">
                        <input type="date" id="date" name="scheduled_date"
                               value="{{ old('scheduled_date', $purchaseData->scheduled_date  ?? '') }}"
                               class="form-control @error('scheduled_date') is-invalid @enderror" aria-label="廠商預計進貨日期"
                               required/>
                        <button class="btn btn-outline-secondary icon" type="button" data-clear
                                data-bs-toggle="tooltip" title="清空日期"><i class="bi bi-calendar-x"></i>
                        </button>
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
                <table class="table table-hover tableList mb-1">
                    <thead>
                    <tr>
                        <th scope="col" class="text-center">刪除</th>
                        <th scope="col">商品名稱</th>
                        <th scope="col">SKU</th>
                        <th scope="col">採購數量</th>
                        <th scope="col">採購價錢</th>
                        <th scope="col">備註</th>
                    </tr>
                    </thead>
                    <tbody class="-appendClone --selectedP">
                    @if (0 >= count(old('item_id', $purchaseItemData?? [])))
                        <tr class="-cloneElem --selectedP d-none">
                            <th class="text-center">
                                <button type="button"
                                        class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <input type="hidden" name="item_id[]" value="">
                                <input type="hidden" name="product_style_id[]" value="">
                                <input type="hidden" name="name[]" value="">
                                <input type="hidden" name="sku[]" value="">
                            </th>
                            <td data-td="name"></td>
                            <td data-td="sku"></td>
                            <td>
                                <input type="number" class="form-control form-control-sm" name="num[]" min="1" value="" required/>
                            </td>
                            <td>
                                <div class="input-group input-group-sm flex-nowrap">
                                    <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                    <input type="number" class="form-control form-control-sm" name="price[]" min="0" value="" required/>
                                </div>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm -xl" name="memo[]">
                            </td>
                        </tr>
                    @elseif(0 < count(old('item_id', $purchaseItemData?? [])))
                        @foreach (old('item_id', $purchaseItemData ?? []) as $psItemKey => $psItemVal)
                            <tr class="-cloneElem --selectedP">
                                <th class="text-center">
                                    <button type="button"
                                            class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <input type="hidden" name="item_id[]" value="{{ old('item_id.'. $psItemKey, $psItemVal['id']?? '') }}">
                                    <input type="hidden" name="product_style_id[]" value="{{ old('product_style_id.'. $psItemKey, $psItemVal['product_style_id']?? '') }}">
                                    <input type="hidden" name="name[]" value="{{ old('name.'. $psItemKey, $psItemVal['title']?? '') }}">
                                    <input type="hidden" name="sku[]" value="{{ old('sku.'. $psItemKey, $psItemVal['sku']?? '') }}">
                                </th>
                                <td data-td="name">{{ old('name.'. $psItemKey, $psItemVal['title']?? '') }}</td>
                                <td data-td="sku">{{ old('sku.'. $psItemKey, $psItemVal['sku']?? '') }}</td>
                                <td>
                                    <input type="number" class="form-control form-control-sm @error('num.' . $psItemKey) is-invalid @enderror"
                                           name="num[]" value="{{ old('num.'. $psItemKey, $psItemVal['num']?? '') }}" min="1" required/>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm flex-nowrap">
                                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                        <input type="number" class="form-control form-control-sm @error('price.' . $psItemKey) is-invalid @enderror"
                                               name="price[]" value="{{ old('price.'. $psItemKey, $psItemVal['price']?? '') }}" min="0" required/>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm -xl" name="memo[]"
                                           value="{{ old('memo.'. $psItemKey, $psItemVal['memo']?? '') }}"/>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
            <div class="d-grid mt-3">
                @error('sku_repeat')
                <div class="alert alert-danger mt-3">{{ $message }}</div>
                @enderror
                @error('item_error')
                <div class="alert alert-danger mt-3">{{ $message }}</div>
                @enderror
                @if(false == ($isAlreadyFinalPay?? false))
                <button id="addProductBtn" type="button"
                        class="btn btn-outline-primary border-dashed" style="font-weight: 500;">
                    <i class="bi bi-plus-circle bold"></i> 加入商品
                </button>
                @endif
            </div>

        </div>

        @if ($method === 'edit')
            <input type='hidden' name='id' value="{{ old('id', $id) }}"/>

            <div class="card shadow p-4 mb-4">
                <h6>付款單</h6>
                <div class="row">
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">訂金付款單</label>
                        <div class="form-control" readonly>
                            <a href="{{ Route('cms.purchase.pay-deposit', ['id' => $id], true) }}">新增付款單</a>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 mb-3 ">
                        <label class="form-label">尾款付款單</label>
                        <div class="form-control" readonly>
                            <a href="{{ Route('cms.purchase.pay-final', ['id' => $id], true) }}">新增付款單</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @error('item_error')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        <div id="submitDiv">
            <div class="col-auto">
                <input type="hidden" name="del_item_id">
                @if(false == ($isAlreadyFinalPay?? false) && (null == ($purchaseData?? null) || null == $purchaseData->close_date))
                <button type="submit" class="btn btn-primary px-4">儲存</button>
                @endif
                <a href="{{ Route('cms.purchase.index', [], true) }}" class="btn btn-outline-primary px-4"
                   role="button">返回列表</a>
            </div>
        </div>
    </form>

    {{-- 商品清單 --}}
    <x-b-modal id="addProduct" cancelBtn="false" size="modal-xl modal-fullscreen-lg-down">
        <x-slot name="title">選取商品加入採購清單</x-slot>
        <x-slot name="body">
            <div class="input-group mb-3 -searchBar">
                <input type="text" class="form-control" placeholder="請輸入名稱或SKU" aria-label="搜尋條件">
                <button class="btn btn-primary" type="button">搜尋商品</button>
            </div>
            {{-- <div class="row justify-content-end mb-2">
                <div class="col-auto">
                    顯示
                    <select class="form-select d-inline-block w-auto" id="dataPerPageElem" aria-label="表格顯示筆數">
                        @foreach (config('global.dataPerPage') as $value)
                            <option value="{{ $value }}">{{ $value }}</option>
                        @endforeach
                    </select>
                    筆
                </div>
            </div> --}}
            <div class="table-responsive">
                <table class="table table-hover tableList">
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
                    <tbody class="-appendClone --product">
                    <tr>
                        <th class="text-center">
                            <input class="form-check-input" type="checkbox"
                                   value="" data-td="p_id" aria-label="選取商品">
                        </th>
                        <td data-td="name">【喜鴻嚴選】咖啡候機室(10入/盒)</td>
                        <td data-td="spec">綜合口味</td>
                        <td data-td="sku">AA2590</td>
                        <td>58</td>
                        <td>20</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="col d-flex justify-content-end align-items-center flex-wrap -pages"></div>
            <div class="alert alert-secondary mx-3 mb-0 -emptyData" style="display: none;" role="alert">
                查無資料！
            </div>
        </x-slot>
        <x-slot name="foot">
            <span class="me-3 -checkedNum">已選取 0 件商品</span>
            <button type="button" class="btn btn-primary btn-ok">加入採購清單</button>
        </x-slot>
    </x-b-modal>
@endsection
@once
    @push('sub-scripts')
        <script>
            let supplierList = @json($supplierList);
            let isAlreadyFinalPay = @json($isAlreadyFinalPay?? false);

            if (true == isAlreadyFinalPay) {
                $('.-cloneElem.--selectedP :input').prop("disabled", true);
            }

            $('#supplier').on('change', function (e) {
                // if ("" != $('input[name=bank_cname]').val()
                //     || "" != $('input[name=bank_code]').val()
                //     || "" != $('input[name=bank_acount]').val()
                //     || "" != $('input[name=bank_numer]').val()) {
                //     if (confirm('下方已設定匯款資訊 是否根據所選廠商做變更?'))
                //     {
                //         changeRemittance();
                //     }
                // } else {
                //     changeRemittance();
                // }
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

            // 儲存前設定name
            $('#form1').submit(function(e) {
                $('input:hidden[name="supplier"]').val($('#supplier').val());
            });
        </script>
        <script>
            let addProductModal = new bootstrap.Modal(document.getElementById('addProduct'));
            let prodPages = new Pagination($('#addProduct .-pages'));
            /*** 選取商品 ***/
            let selectedProductSku = [];
            let selectedProduct = [];
            // clone 項目
            const $selectedClone = $('.-cloneElem.--selectedP:first-child').clone();
            $('.-cloneElem.--selectedP.d-none').remove();

            /*** 刪除商品 ***/
            let del_item_id = [];
            let delItemOption = {
                appendClone: '.-appendClone.--selectedP',
                cloneElem: '.-cloneElem.--selectedP',
                beforeDelFn: function ({$this}) {
                    const item_id = $this.siblings('input[name="item_id[]"]').val();
                    if (item_id) {
                        del_item_id.push(item_id);
                        $('input[name="del_item_id"]').val(del_item_id.toString());
                    }
                },
                checkFn: function () {
                    if ($('.-cloneElem.--selectedP').length) {
                        $('#supplier').prop('disabled', true);
                        $('button[type="submit"]').prop('disabled', false);
                    } else if (@json($method) === 'create') {
                        $('#supplier').prop('disabled', false);
                    }
                    // 無商品不可儲存
                    if (!$('.-cloneElem.--selectedP').length) {
                        $('button[type="submit"]').prop('disabled', true);
                    }
                }
            };
            Clone_bindDelElem($('.-cloneElem.--selectedP .-del'), delItemOption);
            // 無商品不可儲存
            if (!$('.-cloneElem.--selectedP').length) {
                $('button[type="submit"]').prop('disabled', true);
            }

            // 加入商品、搜尋商品
            $('#addProductBtn, #addProduct .-searchBar button')
                .off('click').on('click', function (e) {
                selectedProductSku = [];
                selectedProduct = [];
                // 不檢查重複
                // $('.-cloneElem.--selectedP input[name="sku[]"]').each(function (index, element) {
                //     selectedProductSku.push($(element).val());
                // });
                if (getProductList(1) && $(this).attr('id') === 'addProductBtn') {
                    addProductModal.show();
                }
            });

            // 商品清單 API
            function getProductList(page) {
                let _URL = `${Laravel.apiUrl.productStyles}?page=${page}`;
                let Data = {
                    keyword: $('#addProduct .-searchBar input').val(),
                    supplier_id: $('#supplier').val()
                };

                if (!Data.supplier_id) {
                    toast.show('請先選擇採購廠商。', {type: 'warning', title: '條件未設'});
                    return false;
                } else {
                    $('#addProduct tbody.-appendClone.--product').empty();
                    $('#addProduct #pageSum').text('');
                    $('#addProduct .page-item:not(:first-child, :last-child)').remove();
                    $('#addProduct nav').hide();
                    $('#addProduct .-checkedNum').text(`已選取 ${selectedProductSku.length} 件商品`);

                    axios.post(_URL, Data)
                        .then((result) => {
                            const res = result.data;
                            if (res.status === '0' && res.data && res.data.length) {
                                $('.-emptyData').hide();
                                (res.data).forEach(prod => {
                                    createOneProduct(prod);
                                });
                                // bind event
                                $('#addProduct .-appendClone.--product input[type="checkbox"]:not(:disabled)')
                                    .off('change').on('change', function () {
                                    catchCheckedProduct();
                                    $('#addProduct .-checkedNum').text(`已選取 ${selectedProductSku.length} 件商品`);
                                });

                                // 產生分頁
                                prodPages.create(res.current_page, {
                                    totalData: res.total,
                                    totalPages: res.last_page,
                                    changePageFn: getProductList
                                });
                            } else {
                                $('#addProduct .-emptyData').show();
                            }
                        }).catch((err) => {
                        console.log(err);
                    });

                    return true;

                    // 商品列表
                    function createOneProduct(p) {
                        let checked = (selectedProductSku.indexOf((p.sku).toString()) < 0) ? '' : 'checked disabled';
                        let $tr = $(`<tr>
                            <th class="text-center">
                                <input class="form-check-input" type="checkbox" ${checked}
                                    value="${p.id}" data-td="p_id" aria-label="選取商品">
                            </th>
                            <td data-td="name">${p.product_title}</td>
                            <td data-td="spec">${p.spec || ''}</td>
                            <td data-td="sku">${p.sku}</td>
                            <td>${p.in_stock}</td>
                            <td>${p.safety_stock}</td>
                        </tr>`);
                        $('#addProduct .-appendClone.--product').append($tr);
                    }
                }
            }

            // 紀錄 checked product
            function catchCheckedProduct() {
                $('#addProduct tbody input[data-td="p_id"]').each(function (index, element) {
                    // element == this
                    const sku = $(element).parent('th').siblings('[data-td="sku"]').text();
                    const idx = selectedProductSku.indexOf(sku);
                    if ($(element).prop('checked')) {
                        if (idx < 0) {
                            selectedProductSku.push(sku);
                            selectedProduct.push({
                                id: $(element).val(),
                                name: $(element).parent('th').siblings('[data-td="name"]').text(),
                                sku: sku,
                                spec: $(element).parent('th').siblings('[data-td="spec"]').text()
                            });
                        }
                    } else {
                        if (idx >= 0) {
                            selectedProductSku.splice(idx, 1);
                            selectedProduct.splice(idx, 1);
                        }
                    }

                });
            }

            // btn - 加入採購清單
            $('#addProduct .btn-ok').off('click').on('click', function () {
                selectedProduct.forEach(p => {
                    if (!$(`tr.-cloneElem.--selectedP button[data-id="${p.id}"]`).length) {
                        createOneSelected(p);
                    }
                });
                if ($('.-cloneElem.--selectedP').length) {
                    $('#supplier').prop('disabled', true);
                }

                // 關閉懸浮視窗
                addProductModal.hide();

                // 加入採購單 - 加入一個商品
                function createOneSelected(p) {
                    Clone_bindCloneBtn($selectedClone, function (cloneElem) {
                        cloneElem.find('input').val('');
                        // cloneElem.find('input[name="item_id[]"]').remove();
                        cloneElem.find('.-del').attr('data-id', null);
                        cloneElem.find('td[data-td]').text('');
                        cloneElem.find('.is-invalid').removeClass('is-invalid');
                        if (p) {
                            cloneElem.find('input[name="product_style_id[]"]').val(p.id);
                            cloneElem.find('input[name="name[]"]').val(`${p.name}-${p.spec}`);
                            cloneElem.find('input[name="sku[]"]').val(p.sku);
                            cloneElem.find('td[data-td="name"]').text(`${p.name}-${p.spec}`);
                            cloneElem.find('td[data-td="sku"]').text(p.sku);
                        }
                    }, delItemOption);
                }
            });
            // 關閉Modal時，清空值
            $('#addProduct').on('hidden.bs.modal', function (e) {
                selectedProductSku = [];
                selectedProduct = [];
                $('#addProduct .-searchBar input').val('');
                $('#addProduct tbody.-appendClone.--product').empty();
                $('#addProduct #pageSum').text('');
                $('#addProduct .page-item:not(:first-child, :last-child)').remove();
                $('#addProduct nav').hide();
                $('#addProduct .-checkedNum').text('已選取 0 件商品');
                $('.-emptyData').hide();
            });
        </script>
    @endpush
@endonce

