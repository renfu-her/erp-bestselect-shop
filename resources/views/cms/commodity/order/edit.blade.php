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
                        <input type="hidden" name="customer_id">
                        <select id="customer" class=" form-select -select2 -single" disabled data-placeholder="請選擇訂購客戶">
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @if ($customer->id == $customer_id) selected @endif>
                                    {{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">銷售通路</label>
                        <select id="salechannel" class="form-select">
                            @foreach ($salechannels as $salechannel)
                                <option value="{{ $salechannel->sale_channel_id }}">
                                    {{ $salechannel->sale_channel_title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="">
                    <button id="addProductBtn" type="button" class="btn btn-primary" style="font-weight: 500;">
                        加入商品
                    </button>
                </div>
            </div>
            <div id="Loading_spinner" class="d-flex justify-content-center mb-4" hidden>
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
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
                                        <td>
                                            <div data-td="title"><a href="#" class="-text"></a></div>
                                            <div data-td="discount" class="lh-1 small text-secondary">
                                                <span class="badge rounded-pill bg-danger fw-normal me-2"></span>
                                            </div>
                                        </td>
                                        <td class="text-center" data-td="price">${{ number_format(0) }}</td>
                                        <td>
                                            <x-b-qty-adjuster name="qty[]" value="1" min="1" size="sm" minus="減少" plus="增加">
                                            </x-b-qty-adjuster>
                                        </td>
                                        <td class="text-end">
                                            <div data-td="subtotal">${{ number_format(0) }}</div>
                                            <div data-td="disprice" class="lh-1 text-danger"></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- 運費 --}}
                    <div class="card-body px-4 py-2 border-top">
                        <div class="d-flex lh-lg">
                            <div scope="col" class="col">運費</div>
                            <div class="co-auto" data-td="dlv_fee">${{ number_format(0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card shadow p-4 mb-4">
                <div class="row">
                    <fieldset class="col-12 mb-3">
                        <legend class="col-form-label p-0 mb-2">優惠使用（二擇一）</legend>
                        <div class="px-1 pt-1">
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="coupon_type" type="radio" value="coupon">
                                    優惠券
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="coupon_type" type="radio" value="code">
                                    優惠代碼
                                </label>
                            </div>

                        </div>
                    </fieldset>
                    <div class="col-12 mb-3 --ctype -coupon" hidden>
                        <select class="form-select" aria-label="Select" name="coupon_sn" disabled>
                            <option value="1">item 1</option>
                            <option value="2">item 2</option>
                            <option value="3">item 3</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3 --ctype -code" hidden>
                        <div class="d-flex -coupon_sn @error('coupon') is-invalid @enderror">
                            <input type="text" class="form-control col -coupon_sn" placeholder="請輸入優惠券代碼" disabled>
                            <input type="hidden" name="coupon_sn" disabled>
                            <button type="button" class="btn btn-outline-primary mx-1 px-4 col-auto -coupon_sn">確認</button>
                        </div>
                        <div class="-feedback -coupon_sn @error('coupon') invalid-feedback @enderror">
                            @error('coupon')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                    <div class="col-12 mb-3" hidden>
                        <label class="form-label">
                            紅利<span class="small text-secondary">（目前紅利點數：11點，可使用紅利上限：10點）</span>
                        </label>
                        <div class="d-flex -bonus_point">
                            <input type="text" class="form-control col -bonus_point" placeholder="請輸入會員紅利折抵點數">
                            <input type="hidden" name="bonus">
                            <button type="button" class="btn btn-outline-primary mx-1 px-4 col-auto -bonus_point">確認</button>
                        </div>
                        <div class="-feedback -bonus_point" hidden></div>
                    </div>
                </div>
            </div>
            <div id="Total_price" class="card shadow p-4 mb-4">
                <div id="Global_discount" hidden>
                    <h6>其他優惠</h6>
                    <div class="table-responsive">
                        <table class="table table-sm text-right align-middle">
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

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
                                <td class="text-danger text-end pe-4" data-td="discount">- ${{ number_format(0) }}
                                </td>
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
                        <input type="text" class="form-control" value="{{ old('ord_name') }}" name="ord_name"
                            placeholder="請輸入購買人姓名" required>
                    </div>
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">電話 <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" value="{{ old('ord_phone') }}" name="ord_phone"
                            placeholder="請輸入購買人電話" required>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">地址 <span class="text-danger">*</span></label>
                        <input type="hidden" name="ord_address">
                        <div class="input-group has-validation">
                            <select name="ord_city_id" class="form-select" style="max-width:20%" required>
                                <option value="">縣市</option>
                                @foreach ($citys as $city)
                                    <option value="{{ $city['city_id'] }}"
                                        @if ($city['city_id'] == old('ord_city_id')) selected @endif>{{ $city['city_title'] }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="ord_region_id" class="form-select" style="max-width:20%" required>
                                <option value="">地區</option>
                                @foreach ($regions['ord'] as $region)
                                    <option value="{{ $region['region_id'] }}"
                                        @if ($region['region_id'] == old('ord_region_id')) selected @endif>
                                        {{ $region['region_title'] }}
                                    </option>
                                @endforeach
                            </select>
                            <input name="ord_addr" type="text" class="form-control" placeholder="請輸入購買人地址"
                                value="{{ old('ord_addr') }}" required>
                            <button class="btn btn-outline-success -format_addr_btn" type="button">格式化</button>
                            <div class="invalid-feedback">
                                @error('record')
                                    {{ $message }}
                                    {{-- 地址錯誤訊息: ord_city_id, ord_region_id, ord_addr --}}
                                @enderror
                                @error('ord_address')
                                    {{ $message }}
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
                        <input type="text" class="form-control" value="{{ old('rec_name') }}" name="rec_name"
                            placeholder="請輸入收件人姓名" required>
                    </div>
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">電話 <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" value="{{ old('rec_phone') }}" name="rec_phone"
                            placeholder="請輸入收件人電話" required>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">地址 <span class="text-danger">*</span></label>
                        <input type="hidden" name="rec_address">
                        <div class="input-group has-validation">
                            <select name="rec_city_id" class="form-select" style="max-width:20%" required>
                                <option value="">縣市</option>
                                @foreach ($citys as $city)
                                    <option value="{{ $city['city_id'] }}"
                                        @if ($city['city_id'] == old('rec_city_id')) selected @endif>{{ $city['city_title'] }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="rec_region_id" class="form-select" style="max-width:20%" required>
                                <option value="">地區</option>
                                @foreach ($regions['rec'] as $region)
                                    <option value="{{ $region['region_id'] }}"
                                        @if ($region['region_id'] == old('rec_region_id')) selected @endif>
                                        {{ $region['region_title'] }}
                                    </option>
                                @endforeach
                            </select>
                            <input name="rec_addr" type="text" class="form-control" placeholder="請輸入收件人地址"
                                value="{{ old('rec_addr') }}" required>
                            <button class="btn btn-outline-success -format_addr_btn" type="button">格式化</button>
                            <div class="invalid-feedback">
                                @error('record')
                                    {{-- 地址錯誤訊息: rec_city_id, rec_region_id, rec_addr --}}
                                @enderror
                                @error('rec_address')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <mark class="fw-light small">
                    <i class="bi bi-exclamation-diamond-fill mx-2 text-warning"></i>請填寫真實姓名、電話及地址，以免無法正常收取貨
                </mark>
                <h6 class="d-flex align-items-end">寄件人
                    <label class="small fw-normal text-body ms-3">
                        <input id="sed_same" class="form-check-input mt-0 me-1" type="checkbox">同購買人
                    </label>
                </h6>
                <div class="row">
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">姓名 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" value="{{ old('sed_name') }}" name="sed_name"
                            placeholder="請輸入寄件人姓名" required>
                    </div>
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">電話 <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" value="{{ old('sed_phone') }}" name="sed_phone"
                            placeholder="請輸入寄件人電話" required>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">地址 <span class="text-danger">*</span></label>
                        <input type="hidden" name="sed_address">
                        <div class="input-group has-validation">
                            <select name="sed_city_id" class="form-select" style="max-width:20%" required>
                                <option value="">縣市</option>
                                @foreach ($citys as $city)
                                    <option value="{{ $city['city_id'] }}"
                                        @if ($city['city_id'] == old('sed_city_id')) selected @endif>{{ $city['city_title'] }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="sed_region_id" class="form-select" style="max-width:20%" required>
                                <option value="">地區</option>
                                @foreach ($regions['sed'] as $region)
                                    <option value="{{ $region['region_id'] }}"
                                        @if ($region['region_id'] == old('sed_region_id')) selected @endif>
                                        {{ $region['region_title'] }}
                                    </option>
                                @endforeach
                            </select>
                            <input name="sed_addr" type="text" class="form-control" placeholder="請輸入寄件人地址"
                                value="{{ old('sed_addr') }}" required>
                            <button class="btn btn-outline-success -format_addr_btn" type="button">格式化</button>
                            <div class="invalid-feedback">
                                @error('record')
                                    {{-- 地址錯誤訊息: sed_city_id, sed_region_id, sed_addr --}}
                                @enderror
                                @error('sed_address')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label mt-3">備註</label>
                        <textarea name="note" class="form-control" rows="3"></textarea>
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
                <blockquote class="blockquote">
                    <h6 class="fs-5"></h6>
                </blockquote>
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
            <button class="btn btn-secondary" data-bs-target="#addProduct" data-bs-toggle="modal"
                data-bs-dismiss="modal">返回列表</button>
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
            getSaleChannel();
            $('#customer').off('change.channel').on('change.channel', function () {
                getSaleChannel();
            });

            // 取得客戶身份
            function getSaleChannel() {
                const _URL = @json(route('api.cms.user.get-customer-salechannels'));
                let Data = {
                    customer_id: $('#customer').val()
                };
                $('#salechannel').empty();

                if (!Data.customer_id) {
                    toast.show('請先選擇訂購客戶。', {type: 'warning', title: '條件未設'});
                    return false;
                } else {
                    axios.post(_URL, Data)
                        .then((result) => {
                            const res = result.data;
                            if (res.status === '0' && res.data && res.data.length) {
                                $('#addProductBtn').prop('disabled', false);
                                (res.data).forEach(sale => {
                                    $('#salechannel').append(
                                        `<option value="${sale.sale_channel_id}">${sale.sale_channel_title}</option>`
                                    );
                                });
                            } else {
                                $('#addProductBtn').prop('disabled', true);
                                $('#salechannel').append('<option value="">未綁定身份（無法購物）</option>');
                            }
                        }).catch((err) => {
                            console.error(err);
                    });
                }
            }

            // 優惠使用
            $('input[name="coupon_type"]').off('change').on('change', function () {
                const type = $('input[name="coupon_type"]:checked').val();
                $('div.--ctype').prop('hidden', true);
                $('div.--ctype select, div.--ctype input').prop('disabled', true);
                $(`div.--ctype.-${type}`).prop('hidden', false);
                $(`div.--ctype.-${type} select, div.--ctype.-${type} input`).prop('disabled', false);
            });

            // 禁用鍵盤 Enter submit
            $('form').on('keydown', ':input:not(textarea)', function(e) {
                return e.key !== 'Enter';
            });
            // 儲存前設定name
            $('#form1').submit(function(e) {
                $('input:hidden[name="customer_id"]').val($('#customer').val());
                $('input:hidden[name$="_address"]').val(function() {
                    const prefix_ = $(this).attr('name').replace('address', '');
                    const city = $(`select[name="${prefix_}city_id"] option:selected`).text().trim();
                    const region = $(`select[name="${prefix_}region_id"] option:selected`).text().trim();
                    const addr = $(`input[name="${prefix_}addr"]`).val();
                    return city + region + addr;
                });
            });
        </script>
        <script>
            let addProductModal = new bootstrap.Modal(document.getElementById('addProduct'));
            let setShipmentModal = new bootstrap.Modal(document.getElementById('setShipment'), {
                backdrop: 'static',
                keyboard: false
            });
            let prodPages = new Pagination($('#addProduct .-pages'));
            // 全館優惠
            const GlobalDiscounts = @json($discounts);
            console.log(GlobalDiscounts);
            // 物流方式
            const EVENT_CLASS = {
                'deliver': 'primary',
                'pickup': 'warning',
                'family': 'success'
            };
            // 優惠類型
            const DISC_METHOD = ['cash', 'percent', 'coupon'];
            // 折扣順序 (不含全館)
            const DISC_PRIORITY = ['optional', 'code'];
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
                // total: '小計(不含折扣)'
                // discount: {'優惠類型_優惠ID': 折扣金額},
                // dis_total: '折扣總金額',
                // dised_total: '折扣後小計(total-dis_total)',
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
            // 商品樣式ID
            let myProductList = {
                // sid: {商品}
            };
            // 優惠資料
            let myDiscountList = {
                optional: {},    // 任選 [暫無]
                global: {},     // 全館 + 優惠券不綁商品
                code: {},     // 優惠券/代碼綁商品
            };
            for (const method of DISC_METHOD) {
                if (GlobalDiscounts[method]) {
                    GlobalDiscounts[method].map(d => {
                        myDiscountList.global[d.id] = d;
                    });
                }
            }
            // 購物車資料
            let myCart = { // 購物車
                // 'category_[group_id]/category_[depots.depot_id]': {
                //     id: '物流ID group_id/depots.depot_id',
                //     name: '物流名稱group_name/depots.depot_name',
                //     type: '物流類型category: pickup|deliver',
                //     temps: '溫層: 常溫|冷凍|冷藏',
                //     products: [商品],
                //     total: '此物流商品金額小計(不含運)',
                //     dis_total: '此物流商品優惠金額小計',
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
            // clone opt
            let cloneProductsOption = {
                appendClone: '.-appendClone.--selectedP',
                cloneElem: '.-cloneElem.--selectedP',
                beforeDelFn: function({
                    $this
                }) {
                    const product_style_id = $this.siblings('input[name="product_style_id[]"]').val();
                    if (product_style_id && myProductList[product_style_id]) {
                        // 刪樣式ID[]
                        delete myProductList[product_style_id];
                        // 刪購物車
                        const type = $this.siblings('input[name="shipment_type[]"]').val();
                        const event_id = $this.siblings('input[name="shipment_event_id[]"]').val();
                        const index = (myCart[`${type}_${event_id}`].products).indexOf(product_style_id);
                        (myCart[`${type}_${event_id}`].products).splice(index, 1);

                        // 檢查若該物流沒商品，則刪除該物流
                        if (myCart[`${type}_${event_id}`].products.length <= 0) {
                            delete myCart[`${type}_${event_id}`];
                            $(`#${type}_${event_id}`).remove();
                        }

                        // 商品優惠
                        checkSNProductDiscount();
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

            //超買ID
            const overbought_id = @json($overbought_id);
            // 購物車
            const oldCart = @json($cart);
            // console.log(oldCart);
            if (oldCart && oldCart.success && oldCart.shipments.length) {
                for (const ship of oldCart.shipments) {
                    const old_ship = {
                        category: ship.category,
                        category_name: ship.category_name,
                        group_id: ship.group_id,
                        group_name: ship.group_name,
                        temps: ship.temps,
                        rules: ship.rules || null,
                    };
                    for (const prod of ship.products) {
                        const old_prod = {
                            pid: prod.product_id,
                            sid: prod.id,
                            name: prod.product_title,
                            spec: prod.spec,
                            sku: prod.sku,
                            price: prod.price,
                            stock: prod.in_stock,
                            qty: Number(prod.qty) || 1
                        };
                        addToCart(old_ship, old_prod);
                    }
                }
            }
            // 優惠
            setGlobalDiscount(0);
            // 計數器
            bindAdjusterBtn();
            $('#Loading_spinner').removeClass('d-flex');
            // 刪除商品
            Clone_bindDelElem($('.-cloneElem.--selectedP .-del'), cloneProductsOption);

            // 無商品不可下一步
            if (!$('.-cloneElem.--selectedP').length) {
                $('#STEP_1 .-next_step').prop('disabled', true);
                $('#customer').prop('disabled', false);
            }

            // 第一步-下一步
            $('#STEP_1 .-next_step').off('click').on('click', function() {
                $('#form1 > nav .nav-link:first-child').removeClass('active');
                $('#form1 > nav .nav-link:last-child').addClass('active');
                $('#STEP_1').prop('hidden', true);
                $('#STEP_2').prop('hidden', false);
            });
            // 第二步-上一步
            $('#STEP_2 .-prev_step').off('click').on('click', function() {
                $('#form1 > nav .nav-link:first-child').addClass('active');
                $('#form1 > nav .nav-link:last-child').removeClass('active');
                $('#STEP_1').prop('hidden', false);
                $('#STEP_2').prop('hidden', true);
            });

            // 加入商品、搜尋商品
            $('#addProductBtn, #addProduct .-searchBar button')
                .off('click').on('click', function(e) {
                    if ($(this).attr('id') === 'addProductBtn') {
                        addProductModal.show();
                    } else {
                        getProductList(1);
                    }
                });

            // 開啟商品列表視窗
            $('#addProduct').on('show.bs.modal', function() {
                selectedProduct = {};
                getProductList(1);
            });
            // 商品清單 API
            function getProductList(page) {
                const _URL = `${Laravel.apiUrl.productStyles}?page=${page}`;
                const Data = {
                    keyword: $('#addProduct .-searchBar input').val(),
                    price: 1,
                    salechannel_id: $('#salechannel').val()
                };
                resetAddProductModal();

                if (!Data.price) {
                    toast.show('客戶未綁定身份，無法訂購。', {
                        type: 'danger',
                        title: '無法訂購'
                    });
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
                                $('#addProduct .-appendClone.--product .-add').on('click', function() {
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
                        let addBtn = '',
                            typeTag = '';

                        if (p.in_stock <= 0) {
                            addBtn = `<span class="text-muted">缺貨</span>`;
                        } else if (!myProductList[p.id]) {
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
                            <td>${formatNumber(p.price)}</td>
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
                            qty: 1,
                            total: 0,
                            discount: [],
                            dis_total: 0,
                            dised_total: 0
                        };
                    }
                }
            }

            // 開啟物流選擇視窗
            $('#setShipment').on('show.bs.modal', function() {
                selectShip = {};
                getShpmentData(selectedProduct.pid);
            });
            // 物流 API
            function getShpmentData(pid) {
                const _URL = `${Laravel.apiUrl.productShipments}`;
                const Data = {
                    product_id: pid
                };
                resetSetShipmentModal();
                $('#setShipment blockquote h6').text(`${selectedProduct.name} [${selectedProduct.spec}]`);
                $('#setShipment figcaption').text(selectedProduct.sku);

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
                                        opts += `<option value="${d.depot_id}">${d.depot_name}</option>`;
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
                                            group_id: Number($('select[name="temp_depots"]').val()) || $(
                                                'select[name="temp_depots"]').val(),
                                            group_name: $('select[name="temp_depots"] option:selected').text()
                                                .trim(),
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
                                    addToCart(selectShip, selectedProduct);
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
            function addToCart(selectShip, selectedProduct) {
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
                        dis_total: 0,
                        rules: selectShip.rules || null,
                        dlv_fee: 0
                    };
                    createNewShip(selectShip);
                }
                // 加入一個商品
                myProductList[selectedProduct.sid] = selectedProduct;
                (myCart[shipKey].products).push(selectedProduct.sid);
                createOneSelected(selectedProduct, selectShip);
                // 商品優惠
                checkSNProductDiscount();
                sumSubtotal($(`input[name="product_style_id[]"][value="${selectedProduct.sid}"]`), selectedProduct.qty);

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
                    const options = {
                        ...cloneProductsOption,
                        appendClone: `#${s.category}_${s.group_id} .-appendClone.--selectedP`
                    };
                    Clone_bindCloneBtn($selectedClone, function(cloneElem) {
                        cloneElem.find('input').val('');
                        cloneElem.find('td[data-td], div[data-td]').text('');
                        cloneElem.find('.is-invalid').removeClass('is-invalid');
                        if (p) {
                            cloneElem.find('input[name="product_id[]"]').val(p.pid);
                            cloneElem.find('input[name="product_style_id[]"]').val(p.sid);
                            cloneElem.find('input[name="shipment_type[]"]').val(s.category);
                            cloneElem.find('input[name="shipment_event_id[]"]').val(s.group_id);
                            cloneElem.find('div[data-td="title"]').html(
                                `<a href="#" class="-text">${p.name}-${p.spec}</a>`
                            );
                            cloneElem.find('td[data-td="price"]').text(`$${formatNumber(p.price)}`);
                            cloneElem.find('div[data-td="subtotal"]').text(`$${formatNumber(p.price * p.qty)}`);
                            let $qty = cloneElem.find('input[name="qty[]"]');
                            $qty.val(p.qty);
                            $qty.attr('max', p.stock);
                            // 超賣
                            if (p.sid == overbought_id) {
                                $qty.addClass('is-invalid');
                                $qty.closest('.input-group').addClass('is-invalid');
                                $qty.closest('.input-group').next('.invalid-feedback').text(`剩餘庫存：${p.stock}`);
                            }
                        }
                    }, options);
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

            // 清空商品 Modal
            function resetAddProductModal() {
                $('#addProduct .-searchBar input').val('');
                $('#addProduct tbody.-appendClone.--product').empty();
                $('#addProduct #pageSum').text('');
                $('#addProduct .page-item:not(:first-child, :last-child)').remove();
                $('#addProduct nav').hide();
                $('#addProduct .-checkedNum').text(`已添加 ${Object.keys(myProductList).length} 件商品`);
                $('.-emptyData').hide();
            }
            // 清空物流 Modal
            function resetSetShipmentModal() {
                $('#setShipment blockquote h6, #setShipment figcaption').text('');
                $('#setShipment fieldset > div').empty();
                $('#setShipment .alert-danger').prop('hidden', true);
                $('#setShipment .btn-ok').prop('disabled', false);
                // console.log(myCart);
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
                        (m_qty > min || isNaN(min)) ? $qty.val(m_qty - 1): $qty.val(min);
                    }
                    if ($this.hasClass('-plus')) {
                        (m_qty < max || isNaN(max)) ? $qty.val(m_qty + 1): $qty.val(max);
                    }

                    sumSubtotal($this, $qty.val());
                    checkSNProductDiscount();
                });
                $('input[name="qty[]"]')
                    .off('keydown.adjust').on('keydown.adjust', function(e) {
                        if (e.key === 'Enter') {
                            $(this).trigger('change');
                        }
                    })
                    .off('change.adjust').on('change.adjust', function() {
                        const $this = $(this);
                        let qty = Number($this.val());
                        const min = Number($this.attr('min'));
                        const max = Number($this.attr('max'));
                        qty = (qty < min) ? min : ((qty > max) ? max : qty);
                        $this.val(qty);

                        sumSubtotal($this, qty);
                        checkSNProductDiscount();
                    });
            }

            // 顯示應付金額
            function setTotalPrice() {
                const { all_total, all_discount, all_dlvFee, all_sum } = sumAllAmount();
                $('#Total_price td[data-td="subtotal"]').text(`$${formatNumber(all_total)}`);
                $('#Total_price td[data-td="discount"]').text(`- $${formatNumber(all_discount)}`);
                $('#Total_price td[data-td="dlv_fee"]').text(`$${formatNumber(all_dlvFee)}`);
                $('#Total_price td[data-td="sum"]').text(`$${formatNumber(all_sum)}`);
            }

            /*** 計算 ***/
            // 計算 單一商品小計
            function sumSubtotal($this, qty) {
                // 修改 myCart 裡的數量
                const style_id = Number($this.closest('tr.-cloneElem').find('input[name="product_style_id[]"]').val());
                myProductList[style_id].qty = Number(qty);
                myProductList[style_id].total = myProductList[style_id].price * Number(qty);
                $this.closest('tr.-cloneElem').find('div[data-td="subtotal"]').text(
                    `$${formatNumber(myProductList[style_id].total)}`);

                sumOneProdDisTotal(style_id);

                const $card = $this.closest('.-detail');
                sumGroupTotal($card.attr('id'));
            }
            // 計算 單一商品優惠小計
            function sumOneProdDisTotal(sid) {
                if (Object.hasOwnProperty.call(myProductList, sid)) {
                    for (const key in myProductList[sid].discount) {
                        myProductList[sid].dis_total += myProductList[sid].discount[key];
                    }
                    myProductList[sid].dised_total = myProductList[sid].total - myProductList[sid].dis_total;
                }
            }
            // 計算 群組小計
            function sumGroupTotal(group_key) {
                let total = 0, dis_total = 0;
                if (myCart[group_key]) {
                    (myCart[group_key].products).forEach(sid => {
                        total += myProductList[sid].total;
                        dis_total += myProductList[sid].dis_total;
                    });
                    myCart[group_key].total = total;
                    myCart[group_key].dis_total = dis_total;

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
                            $(`#${group_key} div[data-td="dlv_fee"]`).text(`${formatNumber(myCart[group_key].dlv_fee)}`);
                            break;
                        default:
                            myCart[group_key].dlv_fee = 0;
                            break;
                    }
                }
                // set 應付金額
                setTotalPrice();
            }

            // 計算 應付金額
            function sumAllAmount() {
                // 商品小計
                let all_total = 0;
                // 折扣
                let all_discount = 0;
                // 運費
                let all_dlvFee = 0;
                // 總金額
                let all_sum = 0;

                for (const key in myCart) {
                    if (Object.hasOwnProperty.call(myCart, key)) {
                        const cart = myCart[key];
                        all_total += cart.total;
                        all_dlvFee += cart.dlv_fee;
                        all_discount += cart.dis_total;
                    }
                }
                all_discount += setGlobalDiscount(all_total);
                all_discount = (all_discount > all_total) ? all_total : all_discount;
                all_sum = all_total - all_discount + all_dlvFee;

                return { all_total, all_discount, all_dlvFee, all_sum };
            }

            // 計算 優惠商品總額 (pid = [] 則不限)
            function sumPordTotal(pids = []) {
                let total = 0;
                for (const sid in myProductList) {
                    if (Object.hasOwnProperty.call(myProductList, sid)) {
                        const prod = myProductList[sid];
                        if (pids.length === 0 || pids.indexOf(prod.pid) >= 0) {
                            total += prod.total;
                        }
                    }
                }
                return total;
            }

            /*** 優惠 ***/
            $('button.-coupon_sn').off('click').on('click', function () {
                $('div.--ctype.-code input[name="coupon_sn"]').val('');
                myDiscountList.code = {};
                resetProductDiscount();
                checkCouponSN();
            });
            // 優惠券代碼 -coupon_sn
            function checkCouponSN(sn = '') {
                const _URL = @json(route('api.cms.discount.check-discount-code'));
                let Data = {
                    sn: sn || $('input.-coupon_sn').val(),
                    product_id: []
                };
                // init
                $('.d-flex.-coupon_sn, input.-coupon_sn').removeClass('is-valid is-invalid');
                $('.-feedback.-coupon_sn').removeClass('valid-feedback invalid-feedback').prop('hidden', true);

                if (!Data.sn) {
                    toast.show('請輸入優惠代碼', { type: 'danger' });
                    return false;
                }
                $('input[name="product_id[]"]').each(function (index, element) {
                    // element == this
                    if ((Data.product_id).indexOf($(element).val()) < 0) {
                        (Data.product_id).push($(element).val());
                    }
                });
                if (Data.product_id.length <= 0) {
                    toast.show('請先加入商品', { type: 'danger' });
                    return false;
                }
                Data.product_id = (Data.product_id).toString();

                axios.post(_URL, Data)
                    .then((result) => {
                        const res = result.data;
                        console.log(res);
                        let valid_cls = '', msg = '';
                        if (res.status === '0') {
                            const dis = res.data;
                            let consume = 0;    // 優惠商品小計
                            let discount_price = 0; // 折扣總金額
                            if (dis.is_global) {
                                myDiscountList.global[dis.id] = dis;
                                consume = sumPordTotal();
                                setGlobalDiscount(consume);
                                discount_price = discountUse(dis, consume);
                            } else {
                                myDiscountList.code[dis.id] = dis;
                                consume = sumPordTotal(dis.product_ids);
                                setProductDiscount();   // 回填各商品折扣
                                discount_price = codeDiscountUse(dis, 'code');
                            }
                            // 紀錄sn
                            $('div.--ctype.-code input[name="coupon_sn"]').val(Data.sn);
                            // 檢查低消
                            if (discount_price) {
                                valid_cls = 'valid';
                                msg = `使用優惠券：${dis.title}－`;
                            } else {
                                valid_cls = 'invalid';
                                msg = '未達優惠使用條件：';
                            }
                            msg += discountNote(dis);
                        } else {
                            valid_cls = 'invalid';
                            msg = res.message || '未達優惠使用條件';
                            // 清空sn
                            $('div.--ctype.-code input[name="coupon_sn"]').val('');
                        }
                        $('.d-flex.-coupon_sn, input.-coupon_sn').addClass(`is-${valid_cls}`);
                        $('.-feedback.-coupon_sn').addClass(`${valid_cls}-feedback`)
                            .prop('hidden', false).text(msg);
                    }).catch((err) => {
                        // 清空sn
                        $('div.--ctype.-code input[name="coupon_sn"]').val('');
                        console.error(err);
                    });
            }

            // 紅利 -bonus_point

            // set 全館優惠
            function setGlobalDiscount(all_total = 0) {
                if (Object.keys(myDiscountList.global).length === 0) {
                    return false;
                }
                // 清空
                $('#Global_discount table tbody').empty();

                let total = 0;      // 含優惠券總和
                let maxDiscount = 0;  // 現金、趴數 最優擇一(不含優惠券)
                let bestDiscount = '';  // 最優優惠
                let couponList = [];    // 優惠券可重複
                for (const key in myDiscountList.global) {
                    if (Object.hasOwnProperty.call(myDiscountList.global, key)) {
                        appendTbody(myDiscountList.global[key]);
                    }
                }
                total += maxDiscount;

                if (bestDiscount || couponList.length) {
                    $('#Global_discount').prop('hidden', false);
                    $('#Global_discount table tbody').append(bestDiscount, ...couponList);
                } else {
                    $('#Global_discount').prop('hidden', true);
                }

                return total;

                // 整理可使用優惠 <tr>s
                function appendTbody(dis) {
                    let discount = discountUse(dis, all_total);
                    if (discount) {
                        if (typeof discount !== 'number') {
                            couponList.push(discountTR(dis, discount, 'text-primary'));
                        } else if (dis.category_code !== 'normal') {
                            couponList.push(discountTR(dis, '- $' + discount, 'text-danger'));
                            total += discount;
                        } else if (discount > maxDiscount) {
                            maxDiscount = discount;
                            bestDiscount = discountTR(dis, '- $' + discount, 'text-danger');
                        }
                    }
                }

                // 產生優惠 tr
                function discountTR(dis, dis_text, dis_class) {
                    return `<tr data-id="${dis.id}">
                        <td class="col-8">${dis.title}
                            <span class="small text-secondary">－${discountNote(dis)}</span>
                        </td>
                        <td class="text-end pe-4 ${dis_class}">${dis_text}</td>
                    </tr>`;
                }
            }

            // 清空 所有各商品優惠
            function resetProductDiscount() {
                $(`.-cloneElem.--selectedP div[data-td="discount"],
                   .-cloneElem.--selectedP div[data-td="disprice"]`).remove();
                for (const sid in myProductList) {
                    if (Object.hasOwnProperty.call(myProductList, sid)) {
                        myProductList[sid].discount = {};
                        myProductList[sid].dis_total = 0;
                        myProductList[sid].dised_total = myProductList[sid].total;
                    }
                }
            }

            // set 所有各商品優惠
            function setProductDiscount() {
                if (Object.keys(myDiscountList.code).length === 0 && Object.keys(myDiscountList.optional).length === 0) {
                    return false;
                }
                // 清空
                resetProductDiscount();

                // main: 紀錄商品優惠
                for (const type of DISC_PRIORITY) {
                    let tempDis = myDiscountList[type];
                    if (tempDis && Object.keys(tempDis).length) {
                        for (const id in tempDis) {
                            if (Object.hasOwnProperty.call(tempDis, id)) {
                                codeDiscountUse(tempDis[id], type);
                            }
                        }
                    }
                }
                // 優惠加總
                for (const sid in myProductList) {
                    sumOneProdDisTotal(sid);
                    // set html
                    const prod = myProductList[sid];
                    const $tr = $(`input[name="product_style_id[]"][value="${prod.sid}"]`).closest('.-cloneElem.--selectedP');
                    for (const key in prod.discount) {
                        if (Object.hasOwnProperty.call(prod.discount, key)) {
                            const [type, id] = key.split('_');
                            $tr.find('div[data-td="title"]').after(discountDIV(myDiscountList[type][id]));
                            $tr.find('div[data-td="subtotal"]').after(dispriceDIV(prod.discount[key]));
                        }
                    }
                }
                for (const key in myCart) {
                    sumGroupTotal(key);
                }

                // 折扣文字
                function discountDIV(dis) {
                    return `<div data-td="discount" class="lh-1 small text-secondary">
                        <span class="badge rounded-pill bg-danger fw-normal me-2">已達優惠</span>
                        ${discountNote(dis)}
                    </div>`;
                }
                // 折扣金額
                function dispriceDIV(price) {
                    return `<div data-td="disprice" class="lh-1 text-danger">- $${formatNumber(price)}</div>`;
                }
            }

            // 檢查商品優惠(代碼)
            function checkSNProductDiscount() {
                const sn = $('div.--ctype.-code input[name="coupon_sn"]').val();
                if (sn) {   // 若有優惠碼
                    checkCouponSN(sn);
                } else {    // 若無直接重整優惠
                    setProductDiscount();
                }
            }

            /** 計算總使用各商品優惠
             * @param {object} dis 優惠
             * @param {string} type 類型'optional'|'code'
             * @return 使用總優惠金額
            */
            function codeDiscountUse(dis, type) {
                const pids = dis.product_ids;
                if (!pids || !pids.length) {
                    return false;
                }
                let result = 0;
                let consume = sumPordTotal(pids);
                if (consume >= dis.min_consume) {
                    let total_dis = 0;  // 總折扣金額
                    let ratio = 0;  // 折扣比例
                    switch (dis.method_code) {
                        case 'cash':
                            if (dis.is_grand_total) {   // 累計
                                total_dis = dis.discount_value * (Math.floor(consume / dis.min_consume));
                            } else {
                                total_dis = dis.discount_value;
                            }
                            ratio = consume / total_dis;
                            break;
                        case 'percent':
                            ratio = (100 - dis.discount_value) / 100;
                            break;
                    }

                    let sid = '';
                    for (sid in myProductList) {
                        if (Object.hasOwnProperty.call(myProductList, sid)) {
                            const prod = myProductList[sid];
                            if (pids.indexOf(prod.pid) >= 0) {
                                let price = 0;
                                switch (dis.method_code) {
                                    case 'cash':
                                        price = Math.floor(prod.total / ratio);
                                        total_dis -= price;
                                        break;
                                    case 'percent':
                                        price = Math.floor(prod.total * ratio);
                                        break;
                                }

                                myProductList[sid].discount[`${type}_${dis.id}`] = price;
                                result += price;
                            }
                        }
                    }
                    myProductList[sid].discount[`${type}_${dis.id}`] += total_dis;
                    result += total_dis;
                    return result;
                }
            }

            /** 計算總使用優惠
             * dis: 優惠 {}
             * consume: 優惠商品消費總金額
            */
            function discountUse(dis, consume = 0) {
                let result = false;
                if (consume >= dis.min_consume) {
                    switch (dis.method_code) {
                        case 'cash':
                            if (dis.is_grand_total) {   // 累計
                                let count = Math.floor(consume / dis.min_consume);
                                result = (dis.discount_value * count);
                            } else {
                                result = dis.discount_value;
                            }
                            break;
                        case 'percent':
                            result = Math.floor(consume * (1 - (dis.discount_value / 100)));
                            break;
                        case 'coupon':
                            result = `【${dis.coupon_title || ''}】`;
                            break;
                    }
                }
                return result;
            }

            // ex. 消費滿 $100 折 $10（不得折抵運費），可累計優惠
            // ex. 消費滿 $100 享 88 折優惠（不含運費）
            // ex. 消費不限金額享 88 折優惠（不含運費）
            // ex. 消費滿 $100 送優惠券
            function discountNote(dis) {
                let note = '';
                // 低消
                if (dis.min_consume > 0) {
                    note += `消費滿 $${dis.min_consume} `;
                } else {
                    note += '消費不限金額';
                }
                // 優惠內容
                switch (dis.method_code) {
                    case 'cash':
                        note += `折 $${dis.discount_value}（不得折抵運費）`;
                        break;
                    case 'percent':
                        note += `享 ${dis.discount_value} 折優惠（不含運費）`;
                        break;
                    case 'coupon':
                        note += `送優惠券`;
                        break;
                }
                // 累計
                if (dis.is_grand_total > 0) {
                    note += '，可累計優惠';
                }
                return note;
            }
        </script>
        <script>
            /*** 第二步 ***/
            // 同購買人
            $('#rec_same, #sed_same').off('change').on('change', function() {
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
            $('select[name$="_city_id"]').off('change').on('change', function() {
                const city_id = $(this).val();
                const $regionElem = $(this).next('select[name$="_region_id"]');
                getRegionsAction($regionElem, city_id);
            });
            $('.-format_addr_btn').off('click').on('click', function() {
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
