@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">新增寄倉單</h2>

    @php
        $hasCreatedFinalPayment = $hasCreatedFinalPayment ?? false;
        $consignmentData = $consignmentData ?? null;
    @endphp


    <form id="form1" method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf

        @error('id')
        <div class="alert alert-danger mt-3">{{ $message }}</div>
        @enderror

        <div class="card shadow p-4 mb-4">
            <h6>倉庫資訊</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">出貨倉 <span class="text-danger">*</span></label>

                    <select id="send_depot_id" aria-label="出貨倉" required
                            class="form-select -select2 -single @error('send_depot_id') is-invalid @enderror">
                        <option value="" selected disabled>請選擇</option>
                        @foreach ($depotList as $depot)
                            <option value="{{ $depot->id }}"
                                    @if ($depot->id == old('send_depot_id')) selected @endif>
                                {{ $depot->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">
                        @error('send_depot_id')
                        {{ $message }}
                        @enderror
                    </div>
                    <input type="hidden" name="send_depot_id" value="{{ $consignmentData->send_depot_id ?? '' }}">
                </div>

                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">入庫倉 <span class="text-danger">*</span></label>

                    <select id="receive_depot_id" aria-label="入庫倉" required
                            class="form-select -select2 -single @error('receive_depot_id') is-invalid @enderror">
                        <option value="" selected disabled>請選擇</option>
                        @foreach ($depotList as $depot)
                            <option value="{{ $depot->id }}"
                                    @if ($depot->id == old('receive_depot_id')) selected @endif>
                                {{ $depot->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">
                        @error('receive_depot_id')
                        {{ $message }}
                        @enderror
                    </div>
                    <input type="hidden" name="receive_depot_id" value="{{ $consignmentData->receive_depot_id ?? '' }}">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">預計入庫日期 <span class="text-danger">*</span></label>
                    @if ($hasCreatedFinalPayment)
                        <div class="form-control" readonly>
                            {{ empty($consignmentData->scheduled_date) ? '-' : date('Y/m/d', strtotime($consignmentData->scheduled_date)) }}
                        </div>
                    @else
                        <div class="input-group has-validation">
                            <input type="date" id="scheduled_date" name="scheduled_date"
                                   value="{{ old('scheduled_date', $consignmentData->scheduled_date  ?? '') }}"
                                   class="form-control @error('scheduled_date') is-invalid @enderror" aria-label="寄貨日期"
                                   required/>
                            <button class="btn btn-outline-secondary icon" type="button" data-clear
                                    data-bs-toggle="tooltip" title="清空日期"><i class="bi bi-calendar-x"></i>
                            </button>
                            <div class="invalid-feedback">
                                @error('scheduled_date')
                                {{ $message }}
                                @enderror
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>

        <div class="card shadow p-4 mb-4">
            <h6>寄倉清單</h6>
            <div class="table-responsive tableOverBox">
                <table class="table table-hover tableList mb-0">
                    <thead>
                    <tr>
                        <th scope="col" class="text-center">刪除</th>
                        <th scope="col">商品名稱</th>
                        <th scope="col">SKU</th>
                        <th scope="col">寄倉數量</th>
                        <th scope="col">寄倉價錢</th>
                    </tr>
                    </thead>
                    <tbody class="-appendClone --selectedP">
                    @if (0 >= count(old('item_id', $consignmentItemData?? [])))
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
                                <input type="hidden" name="price[]" value="">
                            </th>
                            <td data-td="name"></td>
                            <td data-td="sku"></td>
                            <td>
                                <input type="number" class="form-control form-control-sm" name="num[]" min="1" value="" required/>
                            </td>
                            <td data-td="price"></td>
                        </tr>
                    @elseif(0 < count(old('item_id', $consignmentItemData?? [])))
                        @foreach (old('item_id', $consignmentItemData ?? []) as $psItemKey => $psItemVal)
                            <tr class="-cloneElem --selectedP">
                                <th class="text-center">
                                    <button type="button"
                                            class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <input type="hidden" name="item_id[]" value="{{ old('item_id.'. $psItemKey, $psItemVal->id?? '') }}">
                                    <input type="hidden" name="product_style_id[]" value="{{ old('product_style_id.'. $psItemKey, $psItemVal->product_style_id?? '') }}">
                                    <input type="hidden" name="name[]" value="{{ old('name.'. $psItemKey, $psItemVal->title?? '') }}">
                                    <input type="hidden" name="sku[]" value="{{ old('sku.'. $psItemKey, $psItemVal->sku?? '') }}">
                                    <input type="hidden" name="price[]" value="{{ old('price.'. $psItemKey, $psItemVal->price?? '') }}">
                                </th>
                                <td data-td="name">{{ old('name.'. $psItemKey, $psItemVal->title?? '') }}</td>
                                <td data-td="sku">{{ old('sku.'. $psItemKey, $psItemVal->sku?? '') }}</td>
                                <td>
                                    <input type="number" class="form-control form-control-sm @error('num.' . $psItemKey) is-invalid @enderror"
                                           name="num[]" value="{{ old('num.'. $psItemKey, $psItemVal->num?? '') }}" min="1" step="1" required/>
                                </td>
                                <td data-td="price">{{ old('price.'. $psItemKey, $psItemVal->price?? '') }}</td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                    <tfoot>
                    <tr>
                        <th class="lh-1"></th>
                        <th class="lh-1"></th>
                        <th class="lh-1"></th>
                        <th class="lh-1">價錢小計</th>
                        <th class="lh-1 text-end -sum">$ 0</th>
                    </tr>
                    </tfoot>
                </table>
            </div>
            <div class="d-grid mt-3">
                @error('sku_repeat')
                <div class="alert alert-danger mt-3">{{ $message }}</div>
                @enderror
                @error('item_error')
                <div class="alert alert-danger mt-3">{{ $message }}</div>
                @enderror
                @if(false == ($hasCreatedFinalPayment?? false))
                    <button id="addProductBtn" type="button"
                            class="btn btn-outline-primary border-dashed" style="font-weight: 500;">
                        <i class="bi bi-plus-circle bold"></i> 加入商品
                    </button>
                @endif
            </div>
        </div>

        @error('del_error')
        <div class="alert alert-danger mt-3">{{ $message }}</div>
        @enderror

        <div id="submitDiv">
            <div class="col-auto">
                <input type="hidden" name="del_item_id">
                <div class="col">
                    @if(null == $consignmentData)
                        <button type="submit" class="btn btn-primary px-4">儲存</button>
                    @elseif(!$hasCreatedFinalPayment && $consignmentData->close_date == null
                        && $consignmentData->audit_status == App\Enums\Consignment\AuditStatus::unreviewed()->value)
                        <button type="submit" class="btn btn-primary px-4">儲存</button>
                    @else
                        {{--判斷已審核 則不可再按儲存--}}
                    @endif
                    <a href="{{ Route('cms.consignment.index', [], true) }}" class="btn btn-outline-primary px-4"
                       role="button">返回列表</a>
                </div>
            </div>
        </div>
    </form>

    {{-- 商品清單 --}}
    <x-b-modal id="addProduct" cancelBtn="false" size="modal-xl modal-fullscreen-lg-down">
        <x-slot name="title">選取商品加入寄倉清單</x-slot>
        <x-slot name="body">
            <div class="input-group mb-3 -searchBar">
                <input type="text" class="form-control" placeholder="請輸入名稱或SKU" aria-label="搜尋條件">
                <button class="btn btn-primary" type="button">搜尋商品</button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover tableList">
                    <thead>
                    <tr>
                        <th scope="col" class="text-center">選取</th>
                        <th scope="col">商品名稱</th>
                        <th scope="col">款式</th>
                        <th scope="col">SKU</th>
                        <th scope="col">出貨倉庫存數量</th>
                        <th scope="col">寄倉價(單價)</th>
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
                        <td data-td="price">99</td>
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
            <button type="button" class="btn btn-primary btn-ok">加入寄倉清單</button>
        </x-slot>
    </x-b-modal>
@endsection
@once
    @push('sub-scripts')
        <script>
            let hasCreatedFinalPayment = @json($hasCreatedFinalPayment?? false);

            if (true == hasCreatedFinalPayment) {
                $('.-cloneElem.--selectedP :input').prop("disabled", true);
            }

            // 物流
            // -新增
            $('#logistics button.-add').off('click').on('click', function () {
                $('#logistics div.row, #logistics button.-del').prop('hidden', false);
                $('#logistics input[name="logistics_price"]').prop('required', true);
                $(this).prop('hidden', true);
            });
            // -刪除
            $('#logistics button.-del').off('click').on('click', function () {
                $('#logistics div.row, #logistics button.-del').prop('hidden', true);
                $('#logistics input[name="logistics_price"]').prop('required', false);
                $('#logistics input[name^="logistics_"]').val('');
                $('#logistics button.-add').prop('hidden', false);
            });

            $('#send_depot_id').on('change', function (e) {
                $('input:hidden[name="send_depot_id"]').val($('#send_depot_id').val());
            });
            $('#receive_depot_id').on('change', function (e) {
                $('input:hidden[name="receive_depot_id"]').val($('#receive_depot_id').val());
            });

            // 儲存前設定name
            $('#form1').submit(function (e) {
                if ($('#send_depot_id').length) {
                    $('input:hidden[name="send_depot_id"]').val($('#send_depot_id').val());
                }
                if ($('#receive_depot_id').length) {
                    $('input:hidden[name="receive_depot_id"]').val($('#receive_depot_id').val());
                }
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
                        $('#send_depot_id').prop('disabled', true);
                        $('#receive_depot_id').prop('disabled', true);
                        $('button[type="submit"]').prop('disabled', false);
                    } else if (@json($method) === 'create') {
                        $('#send_depot_id').prop('disabled', false);
                        $('#receive_depot_id').prop('disabled', false);
                    }
                    // 無商品不可儲存
                    if (!$('.-cloneElem.--selectedP').length) {
                        $('button[type="submit"]').prop('disabled', true);
                    }
                    sumPrice();
                }
            };
            Clone_bindDelElem($('.-cloneElem.--selectedP .-del'), delItemOption);
            // init bind
            bindPriceSum();
            sumPrice();

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
                let _URL = `${Laravel.apiUrl.selectProductList}?page=${page}`;
                let Data = {
                    product_type: 'p',
                    send_depot_id: $('input:hidden[name="send_depot_id"]').val(),
                    receive_depot_id: $('input:hidden[name="receive_depot_id"]').val()
                };

                if (!Data.send_depot_id) {
                    toast.show('請先選擇出貨倉。', {type: 'warning', title: '條件未設'});
                    return false;
                } else if (!Data.receive_depot_id) {
                    toast.show('請先選擇入庫倉。', {type: 'warning', title: '條件未設'});
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
                            <td>${p.total_in_stock_num}</td>
                            <td data-td="price">${p.depot_price}</td>
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
                                spec: $(element).parent('th').siblings('[data-td="spec"]').text(),
                                price: $(element).parent('th').siblings('[data-td="price"]').text()
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

            // btn - 加入寄倉清單
            $('#addProduct .btn-ok').off('click').on('click', function () {
                selectedProduct.forEach(p => {
                    if (!$(`tr.-cloneElem.--selectedP button[data-id="${p.id}"]`).length) {
                        createOneSelected(p);
                    }
                });
                if ($('.-cloneElem.--selectedP').length) {
                    $('#receive_depot_id').prop('disabled', true);
                }
                bindPriceSum();

                // 關閉懸浮視窗
                addProductModal.hide();

                // 加入寄倉單 - 加入一個商品
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
                            cloneElem.find('input[name="price[]"]').val(p.price);
                            cloneElem.find('td[data-td="name"]').text(`${p.name}-${p.spec}`);
                            cloneElem.find('td[data-td="sku"]').text(p.sku);
                            cloneElem.find('td[data-td="price"]').text(p.price);
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

            // 綁定計算
            function bindPriceSum() {
                $('.-cloneElem.--selectedP input[name="price[]"]')
                    .off('change.sum').on('change.sum', function () {
                    sumPrice();
                });
            }
            // 計算小計
            function sumPrice() {
                let sum = 0;
                $('.-cloneElem.--selectedP input[name="price[]"]').each(function (index, element) {
                    // element == this
                    const val = Number($(this).val());
                    sum = Number((sum + val).toFixed(2));
                });
                $('tfoot th.-sum').text(`$ ${formatNumber(sum.toFixed(2))}`);
            }
        </script>
    @endpush
@endonce

