@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">
        @if ($method === 'create')
            新增寄倉訂購單
        @else
            #{{ $breadcrumb_data['sn'] }} 寄倉訂購單
        @endif
    </h2>
    @if ($method === 'edit' and null != $consignmentData)
        <x-b-csnorder-navi :id="$id"></x-b-csnorder-navi>
    @endif

    @php
        $consignmentData = $consignmentData ?? null;

        $editable = false == (isset($delivery) && isset($delivery->audit_date));
    @endphp

    @if ($method === 'edit')
    <nav class="col-12 border border-bottom-0 rounded-top nav-bg">
        <div class="p-1 pe-2">
        @if (!$receivable)
            <a href="{{ Route('cms.ar_csnorder.create', ['id' => $id]) }}" class="btn btn-sm btn-primary" role="button">新增收款單</a>
        @endif
            <a target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-warning"
                href="{{ Route('cms.consignment-order.print_order_ship', ['id' => $id]) . '?type=M1' }}">
                列印出貨單-中一刀
            </a>
            <a target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-warning"
                href="{{ Route('cms.consignment-order.print_order_ship', ['id' => $id]) . '?type=A4' }}">
                列印出貨單-A4
            </a>

            @if (isset($delivery) && isset($delivery->back_date))
                @if (false == isset($delivery->back_inbound_date) && false == ($has_already_pay_delivery_back ?? false))
                    <button type="button"
                            data-href="{{ Route('cms.delivery.back_delete', ['deliveryId' => $delivery->id], true) }}"
                            data-bs-toggle="modal" data-bs-target="#confirm-delete-back"
                            class="btn btn-sm btn-danger -in-header mb-1">
                        刪除退貨
                    </button>
                @endif

                <a class="btn btn-sm btn-success -in-header mb-1"
                   href="{{ Route('cms.delivery.back_detail', ['event' => \App\Enums\Delivery\Event::csn_order()->value, 'eventId' => $delivery->event_id], true) }}">銷貨退回明細</a>

                @can('cms.delivery.edit')
                    @if (isset($delivery->back_inbound_date))
                        <a class="btn btn-sm btn-danger -in-header mb-1"
                           href="{{ Route('cms.delivery.back_inbound_delete', ['deliveryId' => $delivery->id], true) }}">刪除退貨入庫</a>
                    @else
                        <a class="btn btn-sm btn-success -in-header mb-1"
                           href="{{ Route('cms.delivery.back_inbound', ['event' => \App\Enums\Delivery\Event::csn_order()->value, 'eventId' => $delivery->event_id], true) }}">退貨入庫審核</a>
                    @endif
                @endcan

            @else
                <a class="btn btn-sm btn-outline-danger -in-header mb-1"
                   href="{{ Route('cms.delivery.back', ['event' => \App\Enums\Delivery\Event::csn_order()->value, 'eventId' => $delivery->event_id], true) }}">退貨</a>
            @endif
        </div>
    </nav>
    @endif
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
                    <label class="form-label">訂購倉庫 <span class="text-danger">*</span></label>

                    @if ($method === 'edit')
                        <div class="form-control" readonly>
                            {{ $consignmentData->depot_name }}
                        </div>
                    @else
                        <select id="depot_id" aria-label="訂購倉庫" required
                                class="form-select -select2 -single @error('depot_id') is-invalid @enderror">
                            <option value="" selected disabled>請選擇</option>
                            @foreach ($depotList as $depot)
                                <option value="{{ $depot->id }}"
                                        @if ($depot->id == old('depot_id', $consignmentData->depot_id ?? '')) selected @endif>
                                    {{ $depot->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">
                            @error('depot_id')
                            {{ $message }}
                            @enderror
                        </div>
                    @endif
                    <input type="hidden" name="depot_id" value="{{ old('depot_id', $consignmentData->depot_id  ?? '') }}">
                </div>

                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">訂購日期 <span class="text-danger">*</span></label>
                    <div class="input-group has-validation">
                        <input type="date" id="scheduled_date" name="scheduled_date"
                               value="{{ old('scheduled_date', $consignmentData->scheduled_date  ?? date('Y-m-d')) }}"
                               class="form-control @error('scheduled_date') is-invalid @enderror" aria-label="訂購日期"
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
                </div>

                @if ($method === 'create')
                <div class="col-12 mb-3">
                    <label class="form-label">備註</label>
                    <textarea id="order_memo" name="order_memo" class="form-control"
                              @if(null == $consignmentData || (isset($consignmentData) && true == $editable)) @else disabled @endif
                    >{{ old('order_memo', $consignmentData->memo  ?? '') }}</textarea>
                </div>
                @endif
            </div>
        </div>

        @if ($method === 'edit')
            <div class="card shadow p-4 mb-4">
                <h6>訂單資訊</h6>
                <dl class="row">
                    <div class="col">
                        <dt>訂單狀態</dt>
                        <dd>{{ $consignmentData->status ?? '' }}</dd>
                    </div>
                    <div class="col">
                        <dt>付款狀態</dt>
                        <dd>{{ $consignmentData->payment_status_title ?? '' }}</dd>
                    </div>
                    <div class="col">
                        <dt>訂購人</dt>
                        <dd>{{$consignmentData->create_user_name ?? ''}}</dd>
                    </div>
                </dl>
                <dl class="row">
                    <div class="col">
                        <dt>物態</dt>
                        <dd>{{ $logistic_flow->status ?? '' }}</dd>
                    </div>
                    <div class="col">
                        <dt>物態日期</dt>
                        <dd>{{ $logistic_flow->created_at ? date('Y/m/d H:i:s', strtotime($logistic_flow->created_at)) : '' }}</dd>
                    </div>
                    <div class="col">
                        <dt>收貨人名稱</dt>
                        <dd>{{$consignmentData->depot_name ?? ''}}</dd>
                    </div>
                </dl>
                <dl class="row">
                    <div class="col">
                        <dt>收款單號</dt>
                        @if (isset($receivable) && $receivable)
                            <a href="{{ route('cms.ar_csnorder.receipt', ['id' => $consignmentData->id]) }}"
                               class="-text">{{ $received_order_data ? $received_order_data->sn : '' }}</a>
                        @else
                            <span>尚未完成收款</span>
                        @endif
                    </div>
                    <div class="col">
                        <dt>發票類型</dt>
                        <dd>(待處理)</dd>
                    </div>
                    <div class="col"></div>
                </dl>
                <dl class="row">
                    <div class="col">
                        <dt>訂單備註</dt>
                        @if(null == $consignmentData || (isset($consignmentData) && true == $editable))
                            <textarea id="order_memo" name="order_memo" class="form-control" rows="3">{{ old('order_memo', $consignmentData->memo  ?? '') }}</textarea>
                        @else
                            <dd>{{ $consignmentData->memo ?? '' }}</dd>
                        @endif
                    </div>
                </dl>
            </div>
        @endif

        <div class="card shadow p-4 mb-4">
            <h6>寄倉訂購清單</h6>
            <div class="table-responsive tableOverBox">
                <table class="table @if ($editable) table-hover @else table-striped @endif tableList mb-0">
                    <thead>
                    <tr>
                        <th scope="col" class="text-center">刪除</th>
                        <th scope="col">商品名稱</th>
                        <th scope="col">SKU</th>
                        <th scope="col" style="width: 10%">訂購數量</th>
                        <th scope="col" style="width: 12%" class="text-end">訂購價錢</th>
                        <th scope="col" style="width: 12%" class="text-end">小計</th>
                        <th scope="col">備註</th>
                    </tr>
                    </thead>
                    <tbody class="-appendClone --selectedP">
                    @if (0 >= count(old('item_id', $consignmentItemData?? [])) && $editable)
                        <tr class="-cloneElem --selectedP d-none">
                            <th class="text-center">
                                <button type="button"
                                        class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <input type="hidden" name="item_id[]" value="">
                                <input type="hidden" name="product_style_id[]" value="">
                                <input type="hidden" name="name[]" value="">
                                <input type="hidden" name="prd_type[]" value="">
                                <input type="hidden" name="sku[]" value="">
                            </th>
                            <td data-td="name"></td>
                            <td data-td="sku"></td>
                            <td>
                                <input type="number" class="form-control form-control-sm" name="num[]" min="1" value="" required/>
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm" name="price[]" min="0" step="0.01" value="" required>
                            </td>
                            <td data-td="total" class="text-end"></td>
                            <td>
                                <input type="text" class="form-control form-control-sm -l" name="memo[]" />
                            </td>
                        </tr>
                    @elseif(0 < count(old('item_id', $consignmentItemData?? [])))
                        @foreach (old('item_id', $consignmentItemData ?? []) as $psItemKey => $psItemVal)
                            <tr class="-cloneElem --selectedP">
                                @php
                                    $price = old('price.'. $psItemKey, $psItemVal->price?? '');
                                @endphp
                                <th class="text-center">
                                    <button type="button" @if (!$editable) disabled @endif
                                            class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <input type="hidden" name="item_id[]" value="{{ old('item_id.'. $psItemKey, $psItemVal->id?? '') }}">
                                    <input type="hidden" name="product_style_id[]" value="{{ old('product_style_id.'. $psItemKey, $psItemVal->product_style_id?? '') }}">
                                    <input type="hidden" name="name[]" value="{{ old('name.'. $psItemKey, $psItemVal->title?? '') }}">
                                    <input type="hidden" name="prd_type[]" value="{{ old('prd_type.'. $psItemKey, $psItemVal->prd_type?? '') }}">
                                    <input type="hidden" name="sku[]" value="{{ old('sku.'. $psItemKey, $psItemVal->sku?? '') }}">
                                </th>
                                <td data-td="name">{{ old('name.'. $psItemKey, $psItemVal->title?? '') }}</td>
                                <td data-td="sku">{{ old('sku.'. $psItemKey, $psItemVal->sku?? '') }}</td>
                                <td>
                                    @if ($editable)
                                        <input type="number" class="form-control form-control-sm @error('num.' . $psItemKey) is-invalid @enderror"
                                               name="num[]" value="{{ old('num.'. $psItemKey, $psItemVal->num?? '') }}" min="1" step="1" required/>
                                    @else
                                        {{ number_format($psItemVal->num) }}
                                        <input type="hidden" name="num[]" value="{{ $psItemVal->num }}">
                                    @endif
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm @error('price.' . $psItemKey) is-invalid @enderror"
                                        name="price[]" value="{{ old('price.'. $psItemKey, $psItemVal->price?? '') }}">
                                </td>
                                <td data-td="total" class="text-end">$ 0</td>
                                <td>
                                    @if ($editable)
                                        <input type="text" class="form-control form-control-sm -l @error('memo.' . $psItemKey) is-invalid @enderror"
                                               name="memo[]" value="{{ old('memo.'. $psItemKey, $psItemVal->memo?? '') }}"/>
                                    @else
                                        {{ $psItemVal->memo }}
                                        <input type="hidden" name="memo[]" value="{{ $psItemVal->memo }}">
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="lh-1"></th>
                            <th class="lh-1"></th>
                            @if ($editable) <th class="lh-1"></th> @endif
                            <th class="lh-1"></th>
                            <th class="lh-1 text-end">價錢小計</th>
                            <th class="lh-1 text-end -sum">$ 0</th>
                            <th class="lh-1"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @if ($editable)
                <div class="d-grid mt-3">
                    @error('product_style_id.*')
                    <div class="alert alert-danger mt-3">商品SKU不可重複</div>
                    @enderror
                    @error('prd_type.*')
                    <div class="alert alert-danger mt-3">不可選擇庫存零的商品</div>
                    @enderror
                    @error('sku_repeat')
                    <div class="alert alert-danger mt-3">{{ $message }}</div>
                    @enderror
                    @error('item_error')
                    <div class="alert alert-danger mt-3">{{ $message }}</div>
                    @enderror
                    <button id="addProductBtn" type="button"
                            class="btn btn-outline-primary border-dashed" style="font-weight: 500;">
                        <i class="bi bi-plus-circle bold"></i> 加入商品
                    </button>
                </div>
            @endif
        </div>

        @if ($method === 'edit' and true == isset($consume_items) && 0 < count($consume_items))
            <div class="card shadow p-4 mb-4">
                <div class="table-responsive tableOverBox">
                    <div class="card-header text-secondary">物流耗材清單</div>
                    <table class="table tableList table-sm mb-0">
                        <thead class="table-light text-secondary">
                        <tr>
                            <th scope="col">耗材名稱</th>
                            <th scope="col">SKU</th>
                            <th scope="col">數量</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($consume_items as $consume_key => $consume_item)
                            <tr>
                                <td><a href="#" class="-text">{{ $consume_item->product_title }}</a></td>
                                <td>{{ $consume_item->sku }}</td>
                                <td>{{ $consume_item->qty }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @error('del_error')
        <div class="alert alert-danger mt-3">{{ $message }}</div>
        @enderror

        <div id="submitDiv">
            <div class="col-auto">
                <input type="hidden" name="del_item_id">
                <div class="col">
                    @if(null == $consignmentData || (isset($consignmentData) && true == $editable))
                        <button type="submit" class="btn btn-primary px-4">儲存</button>
                    @else
                        {{--判斷已審核 則不可再按儲存--}}
                    @endif
                    <a href="{{ Route('cms.consignment-order.index', [], true) }}" class="btn btn-outline-primary px-4"
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
                    <thead class="small">
                        <tr>
                            <th scope="col" class="text-center">選取</th>
                            <th scope="col">商品名稱</th>
                            <th scope="col">款式</th>
                            <th scope="col">SKU</th>
                            <th scope="col" class="text-end">寄倉庫存<br class="d-block d-lg-none">數量</th>
                            <th scope="col" class="text-end">寄倉價<br class="d-block d-lg-none">(單價)</th>
                        </tr>
                    </thead>
                    <tbody class="-appendClone --product">
                        <tr>
                            <th class="text-center">
                                <input class="form-check-input" type="checkbox"
                                    value="" name="p_id" aria-label="選取商品">
                            </th>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-end"></td>
                            <td class="text-end"></td>
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

    <!-- Modal -->
    <x-b-modal id="confirm-delete">
        <x-slot name="title">刪除確認</x-slot>
        <x-slot name="body">確認要刪除此收款單？</x-slot>
        <x-slot name="foot">
            <a class="btn btn-danger btn-ok" href="#">確認並刪除</a>
        </x-slot>
    </x-b-modal>
@endsection
@once
    @push('sub-styles')
    <style>
        .tableList > :not(caption) > * > * {
            line-height: initial;
        }
    </style>
    @endpush
    @push('sub-scripts')
        <script>

            // Modal Control
            $('#confirm-delete').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });

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

            $('#depot_id').on('change', function (e) {
                $('input:hidden[name="depot_id"]').val($('#depot_id').val());
            });

            // 儲存前設定name
            $('#form1').submit(function (e) {
                if ($('#depot_id').length) {
                    $('input:hidden[name="depot_id"]').val($('#depot_id').val());
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
                        $('#depot_id').prop('disabled', true);
                        $('button[type="submit"]').prop('disabled', false);
                    } else if (@json($method) === 'create') {
                        $('#depot_id').prop('disabled', false);
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
                let _URL = `${Laravel.apiUrl.selectCsnProductList}?page=${page}`;
                let Data = {
                    // product_type: 'p',
                    depot_id: $('input:hidden[name="depot_id"]').val(),
                    keyword: $('#addProduct .-searchBar input').val(),
                };

                if (!Data.depot_id) {
                    toast.show('請先選擇倉庫。', {type: 'warning', title: '條件未設'});
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
                                    value="${p.style_id}" name="p_id" aria-label="選取商品">
                                <input type="hidden" name="sku" value="${p.sku}">
                                <input type="hidden" name="prd_type" value="${p.prd_type}">
                                <input type="hidden" name="product_style_id" value="${p.product_style_id}">
                                <input type="hidden" name="name" value="${p.product_title}">
                                <input type="hidden" name="spec" value="${p.spec || ''}">
                                <input type="hidden" name="price" value="${Number(p.depot_price)}">
                            </th>
                            <td>${p.product_title}</td>
                            <td>${p.spec || ''}</td>
                            <td>${p.sku}</td>
                            <td class="text-end">${p.available_num}</td>
                            <td class="text-end">$${p.depot_price}</td>
                        </tr>`);
                        $('#addProduct .-appendClone.--product').append($tr);
                    }
                }
            }

            // 紀錄 checked product
            function catchCheckedProduct() {
                $('#addProduct tbody input[name="p_id"]').each(function (index, element) {
                    // element == this
                    const sku = $(element).siblings('[name="sku"]').val();
                    const idx = selectedProductSku.indexOf(sku);
                    if ($(element).prop('checked')) {
                        if (idx < 0) {
                            selectedProductSku.push(sku);
                            selectedProduct.push({
                                id: $(element).val(),
                                product_style_id: $(element).siblings('[name="product_style_id"]').val(),
                                name: $(element).siblings('[name="name"]').val(),
                                prd_type: $(element).siblings('[name="prd_type"]').val(),
                                sku: sku,
                                spec: $(element).siblings('[name="spec"]').val(),
                                price: $(element).siblings('[name="price"]').val()
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
                    if (!$(`tr.-cloneElem.--selectedP button[data-id="${p.product_style_id}"]`).length) {
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
                            cloneElem.find('input[name="product_style_id[]"]').val(p.product_style_id);
                            cloneElem.find('input[name="name[]"]').val(`${p.name}-${p.spec}`);
                            cloneElem.find('input[name="prd_type[]"]').val(p.prd_type);
                            cloneElem.find('input[name="sku[]"]').val(p.sku);
                            cloneElem.find('td[data-td="name"]').text(`${p.name}-${p.spec}`);
                            cloneElem.find('td[data-td="sku"]').text(p.sku);
                            cloneElem.find('input[name="price[]"]').val(p.price);
                            cloneElem.find('td[data-td="total"]').text(`$ 0`);
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
                $('.-cloneElem.--selectedP input[name="num[]"], .-cloneElem.--selectedP input[name="price[]"]')
                    .off('change.sum').on('change.sum', function () {
                    sumPrice();
                });
            }
            // 計算小計
            function sumPrice() {
                let sum = 0;
                $('.-cloneElem.--selectedP').each(function (index, element) {
                    // element == this
                    const num = Number($(element).find('input[name="num[]"]').val());
                    const price = Number($(element).find('input[name="price[]"]').val());
                    const total = num * price;
                    $(element).find('td[data-td="total"]').text(`$ ${formatNumber(total.toFixed(2))}`);

                    sum = Number((sum + total).toFixed(2));
                });
                $('tfoot th.-sum, td.-sum').text(`$ ${formatNumber(sum.toFixed(2))}`);
            }
        </script>
    @endpush
@endonce

