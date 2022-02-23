@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">新增訂單</h2>

    <form id="form1" method="post" action="{{ route('cms.order.create') }}">
        @method('POST')
        @csrf
        <nav class="nav nav-pills nav-fill">
            <span class="nav-link active" aria-current="page"><span class="badge -step">第一步</span>添加購物車</span>
            <span class="nav-link"><span class="badge -step">第二步</span>填寫訂購資訊</span>
        </nav>

        <div id="STEP_1">
            <div class="card shadow p-4 mb-4">
                <div class="row">
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">訂購客戶</label>
                        <select id="customer" class=" form-select -select2 -single" disabled data-placeholder="請選擇訂購客戶">
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @if ($customer->id == $customer_id) selected @endif>
                                    {{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 mb-3" hidden>
                        <label class="form-label">客戶身分</label>
                        <input type="hidden" name="customer_id">
                        <div class="form-control" readonly>客戶尚未選取/員工</div>
                    </div>
                </div>
                <div class="">
                    <button id="addProductBtn" type="button" class="btn btn-primary" style="font-weight: 500;">
                        加入商品
                    </button>
                </div>
            </div>
            <div id="MyCart">
                {{-- 宅配 .-detail-primary / 自取 .-detail-warning / 超取 .-detail-success --}}
                <div id="" class="card shadow mb-4 -detail d-none">
                    <div class="card-header px-4 d-flex align-items-center bg-white border-bottom-0">
                        <strong class="flex-grow-1 mb-0"></strong>
                        <span class="badge -badge fs-6"></span>
                    </div>
                    {{-- 商品列表 --}}
                    <div class="card-body px-4 py-0">
                        <div class="table-responsive tableOverBox">
                            <table class="table tableList table-sm mb-0">
                                <thead class="table-light text-secondary">
                                    <tr>
                                        <th scope="col" class="col-1 text-center">刪除</th>
                                        <th scope="col">商品名稱</th>
                                        <th scope="col" class="col-2 text-center">單價</th>
                                        <th scope="col" class="col-2 text-center">數量</th>
                                        <th scope="col" class="col-2 text-end">小計</th>
                                    </tr>
                                </thead>
                                <tbody class="-appendClone --selectedP">
                                    <tr class="-cloneElem --selectedP">
                                        <th>
                                            <button type="button"
                                                class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <input type="hidden" name="product_id[]" value="">
                                            <input type="hidden" name="product_style_id[]" value="">
                                            <input type="hidden" name="shipment_type[]" value="">
                                            <input type="hidden" name="shipment_event_id[]" value="">
                                        </th>
                                        <td data-td="title"><a href="#" class="-text"></a></td>
                                        <td class="text-center" data-td="price">${{ number_format(0) }}</td>
                                        <td>
                                            <x-b-qty-adjuster name="qty[]" value="1" min="1"
                                                size="sm" minus="減少" plus="增加">
                                            </x-b-qty-adjuster>
                                        </td>
                                        <td class="text-end" data-td="subtotal">${{ number_format(0) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- 優惠列表 --}}
                    {{-- <div class="card-body px-4 py-0">
                        <div class="table-responsive tableOverBox">
                            <table class="table tableList table-sm mb-0">
                                <thead class="table-light text-secondary">
                                    <tr>
                                        <th scope="col">優惠類型</th>
                                        <th scope="col">優惠名稱</th>
                                        <th scope="col">贈品</th>
                                        <th scope="col">金額</th>
                                    </tr>
                                </thead>
                                <tbody class="-appendClone">
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div> --}}
                    {{-- 運費 --}}
                    <div class="card-body px-4 py-2 border-top">
                        <div class="d-flex lh-lg">
                            <div scope="col" class="col">運費</div>
                            <div class="co-auto" data-td="dlv_fee">${{ number_format(0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="Total_price" class="card shadow p-4 mb-4">
                <h6>應付金額</h6>
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle d-sm-table d-none text-nowrap">
                        <tbody>
                            <tr class="table-light">
                                <td class="col-2">商品小計</td>
                                <td class="col-2">折扣</td>
                                <td class="col-2">運費</td>
                                <td class="col-2">總金額</td>
                            </tr>
                            <tr>
                                <td data-td="subtotal">${{ number_format(0) }}</td>
                                <td data-td="discount" class="text-danger">- ${{ number_format(0) }}</td>
                                <td data-td="dlv_fee">${{ number_format(0) }}</td>
                                <td data-td="sum" class="fw-bold">${{ number_format(0) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <table class="table table-bordered table-sm text-right align-middle d-table d-sm-none">
                        <tbody>
                            <tr>
                                <td class="col-7 table-light">商品小計</td>
                                <td class="text-end pe-4" data-td="subtotal">${{ number_format(0) }}</td>
                            </tr>
                            <tr>
                                <td class="col-7 table-light">折扣</td>
                                <td class="text-danger text-end pe-4" data-td="discount">- ${{ number_format(0) }}</td>
                            </tr>
                            <tr>
                                <td class="col-7 table-light">運費</td>
                                <td class="text-end pe-4" data-td="dlv_fee">${{ number_format(0) }}</td>
                            </tr>
                            <tr>
                                <td class="col-7 table-light">總金額</td>
                                <td class="fw-bold text-end pe-4" data-td="sum">${{ number_format(0) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-auto">
                <a href="{{ Route('cms.order.index') }}" class="btn btn-outline-primary px-4" role="button">返回列表</a>
                <button type="button" class="btn btn-primary px-4 -next_step">下一步</button>
            </div>
        </div>

        <div id="STEP_2" hidden>
            <div class="card shadow p-4 mb-4">
                <h6>購買人</h6>
                <div class="row">
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">姓名 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="ord_name" placeholder="請輸入購買人姓名" required>
                    </div>
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">電話 <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" name="ord_phone" placeholder="請輸入購買人電話" required>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">地址 <span class="text-danger">*</span></label>
                        <input type="hidden" name="ord_address">
                        <div class="input-group has-validation">
                            <select name="ord_city_id" class="form-select" style="max-width:20%" required>
                                <option value="">縣市</option>
                                @foreach ($citys as $city)
                                    <option value="{{ $city['city_id'] }}" >{{ $city['city_title'] }}</option>
                                @endforeach
                            </select>
                            <select name="ord_region_id" class="form-select" style="max-width:20%" required>
                                <option value="">地區</option>
                            </select>
                            <input name="ord_addr" type="text" class="form-control" placeholder="請輸入購買人地址"
                                value="" required>
                            <button class="btn btn-outline-success -format_addr_btn" type="button">格式化</button>
                            <div class="invalid-feedback">
                                @error('record')
                                    {{-- 地址錯誤訊息: ord_city_id, ord_region_id, ord_addr --}}
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <h6 class="d-flex align-items-end">收件人 
                    <label class="small fw-normal text-body ms-3">
                        <input id="rec_same" class="form-check-input mt-0 me-1" type="checkbox">同購買人
                    </label>
                </h6>
                <div class="row">
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">姓名 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="rec_name" placeholder="請輸入收件人姓名" required>
                    </div>
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">電話 <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" name="rec_phone" placeholder="請輸入收件人電話" required>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">地址 <span class="text-danger">*</span></label>
                        <input type="hidden" name="rec_address">
                        <div class="input-group has-validation">
                            <select name="rec_city_id" class="form-select" style="max-width:20%" required>
                                <option value="">縣市</option>
                                @foreach ($citys as $city)
                                    <option value="{{ $city['city_id'] }}" >{{ $city['city_title'] }}</option>
                                @endforeach
                            </select>
                            <select name="rec_region_id" class="form-select" style="max-width:20%" required>
                                <option value="">地區</option>
                            </select>
                            <input name="rec_addr" type="text" class="form-control" placeholder="請輸入收件人地址"
                                value="" required>
                            <button class="btn btn-outline-success -format_addr_btn" type="button">格式化</button>
                            <div class="invalid-feedback">
                                @error('record')
                                    {{-- 地址錯誤訊息: rec_city_id, rec_region_id, rec_addr --}}
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <h6 class="d-flex align-items-end">寄件人
                    <label class="small fw-normal text-body ms-3">
                        <input id="sed_same" class="form-check-input mt-0 me-1" type="checkbox">同購買人
                    </label>
                </h6>
                <div class="row">
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">姓名 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="sed_name" placeholder="請輸入寄件人姓名" required>
                    </div>
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">電話 <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" name="sed_phone" placeholder="請輸入寄件人電話" required>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">地址 <span class="text-danger">*</span></label>
                        <input type="hidden" name="sed_address">
                        <div class="input-group has-validation">
                            <select name="sed_city_id" class="form-select" style="max-width:20%" required>
                                <option value="">縣市</option>
                                @foreach ($citys as $city)
                                    <option value="{{ $city['city_id'] }}" >{{ $city['city_title'] }}</option>
                                @endforeach
                            </select>
                            <select name="sed_region_id" class="form-select" style="max-width:20%" required>
                                <option value="">地區</option>
                            </select>
                            <input name="sed_addr" type="text" class="form-control" placeholder="請輸入寄件人地址"
                                value="" required>
                            <button class="btn btn-outline-success -format_addr_btn" type="button">格式化</button>
                            <div class="invalid-feedback">
                                @error('record')
                                    {{-- 地址錯誤訊息: sed_city_id, sed_region_id, sed_addr --}}
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-outline-primary px-4 -prev_step">上一步</button>
                <button type="submit" class="btn btn-primary px-4">送出訂單</button>
            </div>
        </div>
    </form>

    {{-- 商品清單 --}}
    <x-b-modal id="addProduct" cancelBtn="false" size="modal-xl modal-fullscreen-lg-down">
        <x-slot name="title">選擇商品</x-slot>
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
                            <th scope="col">商品名稱</th>
                            <th scope="col">款式</th>
                            <th scope="col">SKU</th>
                            <th scope="col">價格</th>
                            <th scope="col">加入購物車</th>
                        </tr>
                    </thead>
                    <tbody class="-appendClone --product">
                        <tr class="-cloneElem d-none">
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>$0</td>
                            <td>
                                <button type="button" class="btn btn-outline-primary -add" data-idx="">
                                    <i class="bi bi-plus-circle"></i> 加入
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col d-flex justify-content-end align-items-center flex-wrap -pages"></div>
            <div class="alert alert-secondary mx-3 mb-0 -emptyData" style="display: none;" role="alert">
                查無商品！
            </div>
        </x-slot>
        <x-slot name="foot">
            <span class="me-3 -checkedNum">已添加 0 件商品</span>
        </x-slot>
    </x-b-modal>

    {{-- 物流選擇 --}}
    <x-b-modal id="setShipment" cancelBtn="false" size="">
        <x-slot name="title">選擇物流</x-slot>
        <x-slot name="body">
            <figure>
                <blockquote class="blockquote"><h6 class="fs-5"></h6></blockquote>
                <figcaption class="blockquote-footer"></figcaption>
            </figure>
            <div>
                <fieldset class="col-12">
                    <div class="ps-1 pe-3"></div>
                </fieldset>
            </div>
            <div class="alert alert-danger" role="alert" hidden>
                <i class="bi bi-exclamation-triangle-fill me-2"></i>目前未提供物流，請返回列表選擇其他商品
            </div>
        </x-slot>
        <x-slot name="foot">
            <button class="btn btn-secondary" data-bs-target="#addProduct" data-bs-toggle="modal" data-bs-dismiss="modal">返回列表</button>
            <button type="button" class="btn btn-primary btn-ok">加入購物車</button>
        </x-slot>
    </x-b-modal>
@endsection
@once
    @push('sub-styles')
        <link rel="stylesheet" href="{{ Asset('dist/css/order.css') }}">
        <style>
            .nav-pills .nav-link {
                border-bottom-left-radius: 0;
                border-bottom-right-radius: 0;
                margin-bottom: -0.25rem;
                padding-bottom: 12px;
            }
            .-detail-primary .badge.-badge::after {
                content: "宅配";
            }
            .-detail-warning .badge.-badge::after {
                content: "自取";
            }
            .-detail-success .badge.-badge::after {
                content: "超取";
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            // 禁用鍵盤 Enter submit
            $('form').on('keydown', function (e) {
                return e.key !== 'Enter';
            });
            // 儲存前設定name
            $('#form1').submit(function(e) {
                $('input:hidden[name="customer_id"]').val($('#customer').val());
                $('input:hidden[name$="_address"]').val(function () {
                    const prefix_ = $(this).attr('name').replace('address', '');
                    const city = $(`select[name="${prefix_}city_id"] option:selected`).text();
                    const region = $(`select[name="${prefix_}region_id"] option:selected`).text();
                    const addr = $(`input[name="${prefix_}addr"]`).val();
                    return city + region + addr;
                });
            });
        </script>
        <script>
            let addProductModal = new bootstrap.Modal(document.getElementById('addProduct'), {
                backdrop: 'static',
                keyboard: false
            });
            let setShipmentModal = new bootstrap.Modal(document.getElementById('setShipment'), {
                backdrop: 'static',
                keyboard: false
            });
            let prodPages = new Pagination($('#addProduct .-pages'));
            // 物流方式
            const EVENT_CLASS = {
                'deliver': 'primary', 'pickup': 'warning', 'family': 'success'
            };
            /*** 選取 ***/
            // 商品
            let selectedProduct = {
                // pid: '產品ID',
                // sid: '樣式ID',
                // name: '商品名稱',
                // spec: '樣式',
                // sku: 'SKU',
                // price: '單價',
                // stock: '庫存',
                // qty: '數量(預設1)',
            };
            // 物流
            let selectShip = {
                // category: '物流類型',
                // category_name: '物流類型中文',
                // group_id: '物流ID',
                // group_name: '物流名稱',
                // temps: '溫層',
                // rules: '宅配價格',
            };
            /** ********* **/
            // 購物車資料
            let productStyleId = []; // 樣式ID
            let myCart = {      // 購物車
                // 'category_[group_id]/category_[depots.id]': {
                //     id: '物流ID group_id/depots.id',
                //     name: '物流名稱group_name/depots.depot_name',
                //     type: '物流類型category: pickup|deliver',
                //     temps: '溫層: 常溫|冷凍|冷藏',
                //     products: [商品],
                //     total: '商品金額小記(不含運)',
                //     rules: '[宅配價格]',
                //     dlv_fee: '運費',
                // }
            };
            // clone 項目
            const $selectedClone = $('.-detail.d-none .-cloneElem.--selectedP').clone();
            $('.-detail.d-none .-appendClone.--selectedP').empty();
            const $cartClone = $('.-detail.d-none').clone();
            $cartClone.removeClass('d-none');
            $('.-detail.d-none').remove();

            /*** init ***/
            // 計數器
            bindAdjusterBtn();

            // 刪除商品
            let delProductsOption = {
                appendClone: '.-appendClone.--selectedP',
                cloneElem: '.-cloneElem.--selectedP',
                beforeDelFn: function({$this}) {
                    const product_style_id = Number($this.siblings('input[name="product_style_id[]"]').val());
                    let index = productStyleId.indexOf(product_style_id);
                    if (product_style_id && index >= 0) {
                        // 刪樣式ID
                        productStyleId.splice(index, 1);
                        // 刪購物車
                        const type = $this.siblings('input[name="shipment_type[]"]').val();
                        const event_id = $this.siblings('input[name="shipment_event_id[]"]').val();
                        myCart[`${type}_${event_id}`].products = 
                        (myCart[`${type}_${event_id}`].products).filter(p => p.sid !== product_style_id);
                        // 檢查若該物流沒商品，則刪除該物流
                        if (myCart[`${type}_${event_id}`].products.length <= 0) {
                            delete myCart[`${type}_${event_id}`];
                            $(`#${type}_${event_id}`).remove();
                        } else {
                            sumGroupTotal(`${type}_${event_id}`);
                        }
                    }
                },
                checkFn: function() {
                    if ($('.-cloneElem.--selectedP').length) {
                        $('#STEP_1 .-next_step').prop('disabled', false);
                        $('#customer').prop('disabled', true);
                    }
                    // 無商品不可下一步
                    if (!$('.-cloneElem.--selectedP').length) {
                        $('#STEP_1 .-next_step').prop('disabled', true);
                        $('#customer').prop('disabled', false);
                    }
                }
            };
            Clone_bindDelElem($('.-cloneElem.--selectedP .-del'), delProductsOption);
            // 無商品不可下一步
            if (!$('.-cloneElem.--selectedP').length) {
                $('#STEP_1 .-next_step').prop('disabled', true);
                $('#customer').prop('disabled', false);
            }

            // 第一步下一步
            $('#STEP_1 .-next_step').off('click').on('click', function () {
                $('#form1 > nav .nav-link:first-child').removeClass('active');
                $('#form1 > nav .nav-link:last-child').addClass('active');
                $('#STEP_1').prop('hidden', true);
                $('#STEP_2').prop('hidden', false);
            });
            // 第二步上一步
            $('#STEP_2 .-prev_step').off('click').on('click', function () {
                $('#form1 > nav .nav-link:first-child').addClass('active');
                $('#form1 > nav .nav-link:last-child').removeClass('active');
                $('#STEP_1').prop('hidden', false);
                $('#STEP_2').prop('hidden', true);
            });

            // 加入商品、搜尋商品
            $('#addProductBtn, #addProduct .-searchBar button')
                .off('click').on('click', function(e) {
                    // productStyleId = [];
                    // // 檢查重複
                    // $('.-cloneElem.--selectedP input[name="product_style_id[]"]').each(function(index, element) {
                    //     productStyleId.push($(element).val());
                    // });
                    if ($(this).attr('id') === 'addProductBtn') {
                        selectedProduct = {};
                        addProductModal.show();
                    } else {
                        getProductList(1);
                    }
                });

            // 開啟商品列表視窗
            $('#addProduct').on('show.bs.modal', function () {
                selectedProduct = {};
                resetAddProductModal();
                getProductList(1);
            });
            // 商品清單 API
            function getProductList(page) {
                let _URL = `${Laravel.apiUrl.productStyles}?page=${page}`;
                let Data = {
                    keyword: $('#addProduct .-searchBar input').val(),
                    price: 1
                };

                if (!Data.price) {
                    toast.show('請先選擇訂購客戶。', { type: 'warning', title: '客戶未選取' });
                    return false;
                } else {
                    axios.post(_URL, Data)
                        .then((result) => {
                            const res = result.data;
                            const prodData = res.data;
                            if (res.status === '0' && prodData && prodData.length) {
                                $('.-emptyData').hide();
                                prodData.forEach((prod, i) => {
                                    createOneProduct(prod, i);
                                });

                                // bind 加入btn
                                $('#addProduct .-appendClone.--product .-add').on('click', function () {
                                    const idx = Number($(this).attr('data-idx'));
                                    setProduct(prodData[idx]);

                                    // 關閉商品懸浮視窗
                                    addProductModal.hide();
                                    // 開啟物流選擇視窗
                                    setShipmentModal.show();
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
                    function createOneProduct(p, i) {
                        let addBtn = '', typeTag = '';

                        if (p.in_stock <= 0) {
                            addBtn = `<span class="text-muted">缺貨</span>`;
                        } else if (productStyleId.indexOf(p.id) < 0) {
                            addBtn = `<button type="button" class="btn btn-outline-primary -add" data-idx="${i}">
                                <i class="bi bi-plus-circle"></i> 加入
                            </button>`;
                        } else {
                            addBtn = `<span class="text-muted">已加入</span>`;
                        }
                        if (p.type_title === '組合包商品') {
                            typeTag = '<span class="badge rounded-pill bg-warning text-dark">組合包</span>';
                        } else {
                            typeTag = '<span class="badge rounded-pill bg-success">一般</span>';
                        }

                        let $tr = $(`<tr>
                            <td>${typeTag} ${p.product_title}</td>
                            <td>${p.spec || ''}</td>
                            <td>${p.sku}</td>
                            <td>$${formatNumber(p.price)}</td>
                            <td>${addBtn}</td>
                        </tr>`);
                        $('#addProduct .-appendClone.--product').append($tr);
                    }
                    // 選擇商品
                    function setProduct(p) {
                        selectedProduct = {
                            pid: p.product_id,
                            sid: p.id,
                            name: p.product_title,
                            spec: p.spec,
                            sku: p.sku,
                            price: p.price,
                            stock: p.in_stock,
                            qty: 1
                        };
                    }
                }
            }

            // 開啟物流選擇視窗
            $('#setShipment').on('show.bs.modal', function () {
                selectShip = {};
                resetSetShipmentModal();
                $('#setShipment blockquote h6').text(`${selectedProduct.name} [${selectedProduct.spec}]`);
                $('#setShipment figcaption').text(selectedProduct.sku);
                getShpmentData(selectedProduct.pid);
            });
            // 物流 API
            function getShpmentData(pid) {
                let _URL = `${Laravel.apiUrl.productShipments}`;
                let Data = {
                    product_id: pid
                };

                axios.post(_URL, Data)
                    .then((result) => {
                        const res = result.data;
                        const shipData = res.data;

                        if (res.status === "0") {
                            $('#setShipment .alert-danger').prop('hidden', true);
                            $('#setShipment .btn-ok').prop('disabled', false);
                            // 宅配
                            if (shipData.deliver) {
                                $('#setShipment fieldset > div').append(`
                                    <div class="form-check mb-3">
                                        <label class="form-check-label">
                                            <input class="form-check-input" name="temp_type" type="radio" value="${shipData.deliver.category}">
                                            ${shipData.deliver.category_name}
                                        </label>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-control" readonly>${shipData.deliver.group_name}</div>
                                            </div>
                                        </div>
                                    </div>
                                `);
                            }
                            // 自取
                            if (shipData.pickup) {
                                $('#setShipment fieldset > div').append(`
                                    <div class="form-check mb-3">
                                        <label class="form-check-label">
                                            <input class="form-check-input" name="temp_type" type="radio" value="${shipData.pickup.category}">
                                            ${shipData.pickup.category_name}
                                        </label>
                                        <div class="row">
                                            <div class="col-12">
                                                <select name="temp_depots" class="form-select">
                                                    <option value="">請選擇</option>
                                                    ${depotsOpts(shipData.pickup.depots)}
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                `);
                                function depotsOpts(depots) {
                                    let opts = '';
                                    depots.forEach(d => {
                                        opts += `<option value="${d.id}">${d.depot_name}</option>`;
                                    });
                                    return opts;
                                }
                            }

                            // bind btn - 加入購物車
                            $('#setShipment .btn-ok').off('click').on('click', function() {
                                const type = $('#setShipment input[name="temp_type"]:checked').val();
                                
                                switch (type) {
                                    case 'deliver':
                                        selectShip = shipData.deliver;
                                        break;
                                    case 'pickup':
                                        if (!$('select[name="temp_depots"]').val()) {
                                            alert('請選擇自取地點。');
                                            selectShip = false;
                                            break;
                                        }
                                        selectShip = {
                                            group_id: Number($('select[name="temp_depots"]').val()) || $('select[name="temp_depots"]').val(),
                                            group_name: $('select[name="temp_depots"] option:selected').text(),
                                            category: shipData.pickup.category,
                                            category_name: shipData.pickup.category_name,
                                            temps: null
                                        };
                                        break;
                                    default:
                                        alert('請選擇物流方式。');
                                        selectShip = false;
                                        break;
                                }
                                if (selectShip) {
                                    addToCart(selectShip);
                                } else {
                                    return false;
                                }
                            });
                        } else {
                            switch (res.status) {
                                case 'empty':
                                    $('#setShipment .alert-danger').prop('hidden', false);
                                    break;
                            
                                default:
                                    break;
                            }
                            $('#setShipment .btn-ok').prop('disabled', true);
                        }
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            }

            // 加入購物車
            function addToCart(selectShip) {
                const shipKey = `${selectShip.category}_${selectShip.group_id}`;
                // 新增一個物流
                if (!myCart[shipKey]) {
                    myCart[shipKey] = {
                        id: selectShip.group_id,
                        name: selectShip.group_name,
                        type: selectShip.category,
                        temps: selectShip.temps,
                        products: [],
                        total: 0,
                        rules: selectShip.rules || null,
                        dlv_fee: 0
                    };
                    createNewShip(selectShip);
                }
                // 加入一個商品
                productStyleId.push(selectedProduct.sid);
                (myCart[shipKey].products).push(selectedProduct);
                createOneSelected(selectedProduct, selectShip);
                sumGroupTotal(shipKey);
                
                if ($('.-cloneElem.--selectedP').length) {
                    $('#customer').prop('disabled', true);
                }

                // 關閉懸浮視窗
                setShipmentModal.hide();

                // 新增一個物流
                function createNewShip(s) {
                    let $newCart = $cartClone.clone();
                    $newCart.addClass(`-detail-${EVENT_CLASS[s.category]}`);
                    $newCart.find('.card-header strong').text(s.group_name);
                    $newCart.attr('id', `${s.category}_${s.group_id}`);
                    if (s.category === 'pickup') { // 自取無價格
                        $newCart.find('div[data-td="dlv_fee"]').text('-');
                    }
                    $('#MyCart').append($newCart);
                }
                // 加入一個商品
                function createOneSelected(p, s) {
                    const cloneProductsOption = {
                        ...delProductsOption,
                        appendClone: `#${s.category}_${s.group_id} .-appendClone.--selectedP`
                    };
                    Clone_bindCloneBtn($selectedClone, function(cloneElem) {
                        cloneElem.find('input').val('');
                        cloneElem.find('td[data-td]').text('');
                        cloneElem.find('.is-invalid').removeClass('is-invalid');
                        if (p) {
                            cloneElem.find('input[name="product_id[]"]').val(p.pid);
                            cloneElem.find('input[name="product_style_id[]"]').val(p.sid);
                            cloneElem.find('input[name="shipment_type[]"]').val(s.category);
                            cloneElem.find('input[name="shipment_event_id[]"]').val(s.group_id);
                            cloneElem.find('input[name="qty[]"]').val(1);
                            cloneElem.find('td[data-td="title"]').html(
                                `<a href="#" class="-text">${p.name}-${p.spec}</a>`
                            );
                            cloneElem.find('td[data-td="price"], td[data-td="subtotal"]').text(`$${formatNumber(p.price)}`);
                            cloneElem.find('input[name="qty[]"]').attr('max', p.stock);
                        }
                    }, cloneProductsOption);
                    // bind click
                    bindAdjusterBtn();
                }
            }

            // 關閉商品Modal時，清空值
            $('#addProduct').on('hidden.bs.modal', function(e) {
                resetAddProductModal();
            });
            // 關閉物流Modal時，清空值
            $('#setShipment').on('hidden.bs.modal', function(e) {
                resetSetShipmentModal();
            });

            // 清空商品Modal
            function resetAddProductModal() {
                $('#addProduct .-searchBar input').val('');
                $('#addProduct tbody.-appendClone.--product').empty();
                $('#addProduct #pageSum').text('');
                $('#addProduct .page-item:not(:first-child, :last-child)').remove();
                $('#addProduct nav').hide();
                $('#addProduct .-checkedNum').text(`已添加 ${productStyleId.length} 件商品`);
                $('.-emptyData').hide();
            }
            // 清空物流Modal
            function resetSetShipmentModal() {
                $('#setShipment blockquote h6, #setShipment figcaption').text('');
                $('#setShipment fieldset > div').empty();
                console.log(myCart);
            }

            // 綁定 計數器按鈕
            function bindAdjusterBtn() {
                // +/- btn
                $('button.-minus, button.-plus').off('click.adjust').on('click.adjust', function() {
                    const $this = $(this);
                    const $qty = $this.siblings('input[name="qty[]"]');
                    const min = Number($qty.attr('min'));
                    const max = Number($qty.attr('max'));
                    const m_qty = Number($qty.val());
                    if ($this.hasClass('-minus')) {
                        (m_qty > min || isNaN(min)) ? $qty.val(m_qty - 1) : $qty.val(min);
                    }
                    if ($this.hasClass('-plus')) {
                        (m_qty < max || isNaN(max)) ? $qty.val(m_qty + 1) : $qty.val(max);
                    }
                    
                    sumSubtotal($this, $qty.val());
                });
                $('input[name="qty[]"]')
                .off('keydown.adjust').on('keydown.adjust', function (e) {
                    if (e.key === 'Enter') {
                        $(this).trigger('change');
                    }
                })
                .off('change.adjust').on('change.adjust', function() {
                    sumSubtotal($(this), $(this).val());
                });
            }

            /** 計算 **/
            // 計算 單一商品小計
            function sumSubtotal($this, qty) {
                // 修改 myCart 裡的數量
                const $card = $this.closest('.-detail');
                const id = $card.attr('id');
                const style_id = Number($card.find('input[name="product_style_id[]"]').val());
                (myCart[id].products).forEach(p => {
                    if (p.sid === style_id) {
                        p.qty = Number(qty);
                        $this.closest('tr.-cloneElem').find('td[data-td="subtotal"]').text(`$${formatNumber(p.price * p.qty)}`);
                    }
                });

                sumGroupTotal(id);
            }
            // 計算 群組小計
            function sumGroupTotal(group_key) {
                let total = 0;
                if (!myCart[group_key]) {
                    return false;
                } else {
                    (myCart[group_key].products).forEach(p => {
                        total += (p.price * p.qty);
                    });
                    myCart[group_key].total = total;

                    // 運費
                    switch (myCart[group_key].type) {
                        case 'deliver':
                            for (const rule of myCart[group_key].rules) {
                                if ((rule.is_above === 'false' && total >= rule.min_price && total < rule.max_price) ||
                                    (rule.is_above === 'true' && total >= rule.max_price)) {
                                    myCart[group_key].dlv_fee = Number(rule.dlv_fee);
                                    break;
                                }
                            }
                            $(`#${group_key} div[data-td="dlv_fee"]`).text(`$${formatNumber(myCart[group_key].dlv_fee)}`);
                            break;
                        default:
                            myCart[group_key].dlv_fee = 0;
                            break;
                    }
                    sumAllAmount();
                }
            }
            // 計算 應付金額
            function sumAllAmount() {
                // 商品小計
                let all_total = 0;
                // 運費
                let all_dlvFee = 0;

                for (const key in myCart) {
                    if (Object.hasOwnProperty.call(myCart, key)) {
                        const cart = myCart[key];
                        all_dlvFee += cart.dlv_fee;
                        all_total += cart.total;
                    }
                }

                $('#Total_price td[data-td="subtotal"]').text(`$${formatNumber(all_total)}`);
                $('#Total_price td[data-td="dlv_fee"]').text(`$${formatNumber(all_dlvFee)}`);
                $('#Total_price td[data-td="sum"]').text(`$${formatNumber(all_total + all_dlvFee)}`);
            }
            
        </script>
        <script>
            /*** 第二步 ***/
            // 同購買人
            $('#rec_same, #sed_same').off('change').on('change', function () {
                const $this = $(this);
                const prefix_ = $this.attr('id').replace(/same/g, '');
                if ($this.prop('checked')) {
                    $(`input[name="${prefix_}name"]`).val($('input[name="ord_name"]').val());
                    $(`input[name="${prefix_}phone"]`).val($('input[name="ord_phone"]').val());
                    $(`input[name="${prefix_}addr"]`).val($('input[name="ord_addr"]').val());
                    $(`select[name="${prefix_}city_id"]`).val($('select[name="ord_city_id"]').val());
                    getRegionsAction(
                        $(`select[name="${prefix_}region_id"]`),
                        $('select[name="ord_city_id"]').val(),
                        $('select[name="ord_region_id"]').val()
                    );
                } else {
                    // 清空
                    $(`input[name="${prefix_}name"],
                       input[name="${prefix_}phone"],
                       input[name="${prefix_}addr"],
                       select[name="${prefix_}city_id"],
                       select[name="${prefix_}region_id"]`).val('');
                    $(`select[name="${prefix_}region_id"]`).html('<option value="">地區</option>');
                }
            });

            // 格式化地址
            function getRegionsAction(regionElem, city_id, region_id) {
                Addr.getRegions(city_id)
                    .then(re => {
                        Elem.renderSelect(regionElem, re.datas, {
                            default: region_id,
                            key: 'region_id',
                            value: 'region_title',
                            defaultOption: '地區'
                        });
                    });
            }
            $('select[name$="_city_id"]').off('change').on('change', function () {
                const city_id = $(this).val();
                const $regionElem = $(this).next('select[name$="_region_id"]');
                getRegionsAction($regionElem, city_id);
            });
            $('.-format_addr_btn').off('click').on('click', function () {
                const $cityElem = $(this).siblings('select[name$="_city_id"]');
                const $regionElem = $(this).siblings('select[name$="_region_id"]');
                const $addrElem = $(this).prev('input[name$="_addr"]');
                const addr_val = $addrElem.val();
                if (addr_val) {
                    Addr.addrFormating(addr_val).then(re => {
                        $addrElem.val(re.data.addr);
                        if (re.data.city_id) {
                            $cityElem.val(re.data.city_id);
                            getRegionsAction($regionElem, re.data.city_id, re.data.region_id);
                        }
                    });
                }
            });
        </script>
    @endpush
@endonce
