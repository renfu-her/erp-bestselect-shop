@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">新增訂單</h2>

    <form id="form1" method="post" action="{{ route('cms.order.create', $query) }}">
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
                        <input type="hidden" name="salechannel_id">
                        <select id="salechannel" class="form-select">
                            @foreach ($salechannels as $salechannel)
                                <option value="{{ $salechannel->id }}">
                                    {{ $salechannel->title }}</option>
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
                                            {{-- 優惠 --}}
                                        </td>
                                        <td class="text-center" data-td="price">${{ number_format(0) }}</td>
                                        <td>
                                            <x-b-qty-adjuster name="qty[]" value="1" min="1" size="sm" minus="減少" plus="增加">
                                            </x-b-qty-adjuster>
                                        </td>
                                        <td class="text-end">
                                            <div data-td="subtotal">${{ number_format(0) }}</div>
                                            {{-- 折扣金額 --}}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- 使用鴻利 --}}
                    <div class="card-body px-4 py-2 border-top">
                        <div class="d-flex lh-lg flex-wrap">
                            <div class="col-12 col-sm pe-2">鴻利
                                <span class="small text-secondary">
                                    （目前鴻利點數：<span class="-hasPoints">0</span>
                                    點，可抵用鴻利上限：<span class="-maxPoints">0</span> 點）
                                </span>
                            </div>
                            <div class="col-12 col-sm-auto">
                                <div class="d-flex -bonus_point">
                                    <input type="number" max="0" min="0" placeholder="使用"
                                        class="form-control form-control-sm col -bonus_point">
                                    <input type="hidden" name="dividend[]">
                                    <input type="hidden" name="dividend_id[]">
                                    <button type="button"
                                        class="btn btn-sm btn-outline-primary mx-1 px-4 col-auto -bonus_point">確認</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- 運費 --}}
                    <div class="card-body px-4 py-2 border-top">
                        <div class="d-flex lh-lg">
                            <div class="col">運費
                                {{-- 運費說明 --}}
                            </div>
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
                            <option value="" selected>請選擇優惠券</option>
                        </select>
                        <span class="-note small text-success"></span>
                    </div>
                    <div class="col-12 mb-3 --ctype -code" hidden>
                        <div class="d-flex -coupon_sn @error('coupon') is-invalid @enderror">
                            <input type="text" class="form-control col -coupon_sn @error('coupon') is-invalid @enderror"
                                placeholder="請輸入優惠券代碼" disabled>
                            <input type="hidden" name="coupon_sn" disabled>
                            <button type="button" class="btn btn-outline-primary mx-1 px-4 col-auto -coupon_sn">確認</button>
                        </div>
                        <div class="-feedback -coupon_sn @error('coupon') invalid-feedback @enderror">
                            @error('coupon')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div id="Total_price" class="card shadow p-4 mb-4">
                <div id="Discount_overview" hidden>
                    <h6>優惠折扣總覽</h6>
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
                    <fieldset class="col-12 mb-1">
                        <div class="px-1 pt-1">
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="ord_radio" value="default" type="radio" checked>
                                    預設地址
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="ord_radio" value="new" type="radio" >
                                    新增地址
                                </label>
                            </div>
                        </div>
                    </fieldset>

                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">姓名 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" value="{{ old('ord_name', $defaultAddress->name ?? '') }}" name="ord_name"
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
                                        @if ($city['city_id'] == old('ord_city_id', $defaultAddress->city_id ?? '')) selected @endif>{{ $city['city_title'] }}
                                    </option>
                                @endforeach
                            </select>
                            @php
                                $default_region = $regions['ord'];
                                if (isset($defaultAddress->city_id)) {
                                    $default_region = App\Models\Addr::getRegions($defaultAddress->city_id ?? '');
                                }
                            @endphp
                            <select name="ord_region_id" class="form-select" style="max-width:20%" required>
                                <option value="">地區</option>
                                @foreach ($default_region as $region)
                                    <option value="{{ $region['region_id'] }}"
                                        @if ($region['region_id'] == old('ord_region_id', $defaultAddress->region_id ?? '')) selected @endif>
                                        {{ $region['region_title'] }}
                                    </option>
                                @endforeach
                            </select>
                            <input name="ord_addr" type="text" class="form-control" placeholder="請輸入購買人地址"
                                value="{{ old('ord_addr', $defaultAddress->addr ?? '') }}" required>
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
                            <th scope="col" width="10%" class="text-center">加入</th>
                            <th scope="col">商品名稱</th>
                            <th scope="col">款式</th>
                            <th scope="col">SKU</th>
                            <th scope="col">價格</th>
                        </tr>
                    </thead>
                    <tbody class="-appendClone --product">
                        <tr class="-cloneElem d-none">
                            <td class="text-center">
                                <button type="button" class="btn btn-outline-primary -add" data-idx="">
                                    <i class="bi bi-plus-circle"></i>
                                </button>
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>$0</td>
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

            .-detail input.-bonus_point {
                min-width: 100px;
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            getSaleChannel();
            $('#customer').off('change.channel').on('change.channel', function() {
                getSaleChannel();
            });

            // 取得客戶身份
            function getSaleChannel() {
                const _URL = @json(route('api.cms.user.get-user-salechannels'));
                let Data = {
                    customer_id: $('#customer').val()
                };
                $('#salechannel').empty();

                if (!Data.customer_id) {
                    toast.show('請先選擇訂購客戶。', {
                        type: 'warning',
                        title: '銷售通路'
                    });
                    return false;
                } else {
                    axios.post(_URL, Data)
                        .then((result) => {
                            const res = result.data;
                            if (res.status === '0' && res.data && res.data.length) {
                                $('#addProductBtn').prop('disabled', false);
                                (res.data)
                                .forEach(sale => {
                                    $('#salechannel').append(
                                        `<option value="${sale.id}">${sale.title}</option>`
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

            // 禁用鍵盤 Enter submit
            $('form').on('keydown', ':input:not(textarea)', function(e) {
                return e.key !== 'Enter';
            });
            // 儲存前設定name
            $('#form1').submit(function(e) {
                $('input:hidden[name="customer_id"]').val($('#customer').val());
                $('input:hidden[name="salechannel_id"]').val($('#salechannel').val());
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
            // 物流方式
            const EVENT_CLASS = {
                'deliver': 'primary',
                'pickup': 'warning',
                'family': 'success'
            };
            /*** 優惠資料 ***/
            // 全館優惠
            const GlobalDiscounts = @json($discounts);
            console.log('全館優惠', GlobalDiscounts);
            // 優惠方式
            const DISC_METHOD = ['cash', 'percent', 'coupon'];
            // 優惠類型 折扣順序：任選 > 全館 > 優惠券/序號 > 鴻利
            const DISC_PRIORITY = ['optional', 'global', 'coupon', 'code', 'bonus'];
            // 訂購會員持有優惠券
            let UserCoupons = {};
            // 訂購會員持有鴻利
            let UserPoints = 0;
            // 目前有效優惠
            let DiscountData = {
                optional: {}, // 任選 [暫無] (固定)
                global: { // 全館 (固定)
                    // id: {}
                },
                coupon: {}, // 優惠券 (變動)
                code: {}, // 優惠代碼 (變動)
            };
            /** global/code:
             * {
                "id": ID,
                "sn": '優惠券序號' (code only),
                "title": "優惠名稱",
                "category_title": "優惠類型名稱",
                "category_code": "優惠類型: optional|global|code|coupon",
                "method_code": "優惠方式: cash|percent|coupon",
                "method_title": "優惠方式名稱",
                "discount_value": 優惠內容 (依 method_code 而定),
                "min_consume": 低消 (0不限),
                "is_grand_total": 是否累計: 否0|是1,
                "coupon_id": 優惠券ID (method_code=coupon only),
                "coupon_title": 優惠券名稱 (method_code=coupon only),
                "max_usage": 使用上限 (0不限)(code only),
                "usage_count": 已使用數量 (code only),
                "is_global": 適用商品群組: 全館1|綁商品0 (code only),
                "product_ids": 適用商品pid (is_global=0 only)
                }
            */
            for (const method of DISC_METHOD) {
                if (GlobalDiscounts[method]) {
                    GlobalDiscounts[method].map(d => {
                        DiscountData.global[d.id] = {
                            ...d,
                            category_code: 'global'
                        };
                    });
                }
            }
            // 未達優惠使用條件
            let notMeetDiscount = [
                /** {
                 * note: '',
                 * pids: []
                 * } */
            ];
            /*** 選取 ***/
            // 商品
            let selectedProduct = {
                /* 固定值 */
                // pid: '產品ID',
                // sid: '樣式ID',
                // name: '商品名稱',
                // spec: '樣式',
                // sku: 'SKU',
                // price: '單價',
                // stock: '庫存',
                // point: '鴻利上限',
                /* 變動值 */
                // qty: 數量(預設1),
                //_total: 小計(不含折扣)(price*qty),
                // discount: {'優惠類型_優惠ID': 折扣金額/優惠券名稱},
                // dis_total: 折扣總金額(discount加總),
                // dised_total: 折扣後小計(total-dis_total),
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
            // 購物車資料
            let myCart = { // 購物車
                // '[category]_[group_id]/[category]_[depots.depot_id]': {
                /* 固定值 */
                //     id: '物流ID group_id/depots.depot_id',
                //     name: '物流名稱group_name/depots.depot_name',
                //     type: '物流類型category: pickup|deliver',
                //     temps: '溫層: 常溫|冷凍|冷藏'(deliver only),
                //     rules: '[宅配價格]'(deliver only),
                /* 變動值 */
                //     point: 使用鴻利,
                //     products: [商品sid],
                //_____total: 此物流商品金額小計(不折扣、含運),
                //     dis_total: 此物流商品折扣總金額,
                //     dised_total: 此物流商品折扣後金額小計(total-dis_total-point),
                //     dlv_fee: 運費(以dised_total判斷),
                // }
            };
            // 已使用優惠
            let myDiscount = {
                // '[優惠類型]_[優惠ID]': {
                /* 固定值 */
                //     id: '優惠ID',
                //     name: '優惠名稱title',
                //     type: '優惠類型: optional|global|code',
                //     method: '優惠方式method_code: cash|percent|coupon',
                //     note: '優惠說明',
                //     code: '優惠券序號'(type=code only),
                //     coupon: '優惠券名稱'(method=coupon only),
                /* 變動值 */
                //     total: 優惠折抵總金額
                // }
            };

            // clone 項目
            const $selectedClone = $('.-detail.d-none .-cloneElem.--selectedP').clone();
            $('.-detail.d-none .-appendClone.--selectedP').empty();
            const $cartClone = $('.-detail.d-none').clone();
            $cartClone.removeClass('d-none');
            $('.-detail.d-none').remove();

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
                        } else {
                            // 否則重計算total
                            myCart[`${type}_${event_id}`].total = calc_ProductTotalBySid(myCart[`${type}_${event_id}`]
                                .products);
                        }

                        // 檢查試算
                        calcAndCheckAllOrder();
                    }
                },
                checkFn: function() {
                    if ($('.-cloneElem.--selectedP').length) {
                        $('#STEP_1 .-next_step').prop('disabled', false);
                        $('#customer, #salechannel').prop('disabled', true);
                    }
                    // 無商品不可下一步
                    if (!$('.-cloneElem.--selectedP').length) {
                        $('#STEP_1 .-next_step').prop('disabled', true);
                        $('#customer, #salechannel').prop('disabled', false);
                    }
                }
            };

            /*** 錯誤返回 ***/
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
                        temps: ship.temps || null,
                        rules: ship.rules || null,
                    };
                    for (const prod of ship.products) {
                        const old_prod = {
                            pid: prod.product_id,
                            sid: prod.id,
                            name: prod.product_title,
                            spec: prod.spec,
                            sku: prod.sku,
                            price: Number(prod.price),
                            stock: Number(prod.in_stock),
                            qty: Number(prod.qty) || 1,
                            total: 0,
                            discount: {},
                            dis_total: 0,
                            dised_total: 0
                        };
                        addToCart(old_ship, old_prod);
                    }
                    const shipKey = `${ship.category}_${ship.group_id}`;
                    myCart[shipKey].total = calc_ProductTotalBySid(myCart[shipKey].products);
                }

                // 檢查試算
                calcAndCheckAllOrder();
            }

            /*** init ***/
            // 計數器
            bindAdjusterBtn();
            // 刪除商品
            Clone_bindDelElem($('.-cloneElem.--selectedP .-del'), cloneProductsOption);
            // 鴻利
            getPointsAPI();
            bindPointUseBtn();

            // 初始結束隱藏loading
            $('#Loading_spinner').removeClass('d-flex');

            /*** bind ***/
            // #加入商品、搜尋商品
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
            // #商品清單 API
            function getProductList(page) {
                const _URL = `${Laravel.apiUrl.productStyles}?page=${page}`;
                const Data = {
                    keyword: $('#addProduct .-searchBar input').val(),
                    price: $('#salechannel').val()
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
                                $('#addProduct .-emptyData').hide();
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
                                <i class="bi bi-plus-circle"></i>
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
                            <td class="text-center">${addBtn}</td>
                            <td>${typeTag} ${p.product_title}</td>
                            <td>${p.spec || ''}</td>
                            <td>${p.sku}</td>
                            <td>${formatNumber(p.price)}</td>
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
                            point: p.dividend,
                            qty: 1,
                            total: 0,
                            discount: {},
                            dis_total: 0,
                            dised_total: 0
                        };
                    }
                }
            }

            // #開啟物流選擇視窗
            $('#setShipment').on('show.bs.modal', function() {
                selectShip = {};
                getShpmentData(selectedProduct.pid);
            });
            // #物流 API
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
                                            category: shipData.pickup.category,
                                            category_name: shipData.pickup.category_name,
                                            group_id: Number($('select[name="temp_depots"]').val()) ||
                                                $('select[name="temp_depots"]').val(),
                                            group_name: $('select[name="temp_depots"] option:selected').text()
                                                .trim(),
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
                                    const shipKey = `${selectShip.category}_${selectShip.group_id}`;
                                    myCart[shipKey].total = calc_ProductTotalBySid(myCart[shipKey].products);

                                    if (DiscountData.code.sn) { // 有使用優惠券
                                        getCouponCheckAPI(DiscountData.code.sn);
                                    } else {
                                        // 檢查試算
                                        calcAndCheckAllOrder();
                                    }
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

            // #關閉商品Modal時，清空值
            $('#addProduct').on('hidden.bs.modal', function(e) {
                resetAddProductModal();
            });
            // #關閉物流Modal時，清空值
            $('#setShipment').on('hidden.bs.modal', function(e) {
                resetSetShipmentModal();
            });

            // #加入購物車 (不含優惠)
            // 0. 新增一個物流
            // 1. 計算商品小計 selectedProduct total
            // 2. 存入 myProductList sid
            // 3. 存入 myCart products
            // 4. 產生 HTML
            function addToCart(selectShip, selectedProduct) {
                const shipKey = `${selectShip.category}_${selectShip.group_id}`;
                // 1.
                if (!myCart[shipKey]) {
                    myCart[shipKey] = {
                        id: selectShip.group_id,
                        name: selectShip.group_name,
                        type: selectShip.category,
                        temps: selectShip.temps,
                        rules: selectShip.rules || null,
                        point: 0,
                        products: [],
                        total: 0,
                        dis_total: 0,
                        dised_total: 0,
                        dlv_fee: 0
                    };
                    createNewShip(selectShip);
                }
                // 1.
                selectedProduct.total = selectedProduct.price * selectedProduct.qty;
                selectedProduct.dised_total = selectedProduct.total;
                // 2. set myProductList
                myProductList[selectedProduct.sid] = selectedProduct;
                // 3. set myCart
                (myCart[shipKey].products).push(selectedProduct.sid);
                // 4. HTML
                createOneSelected(selectedProduct, selectShip);

                console.log('myProductList', myProductList);

                if ($('.-cloneElem.--selectedP').length) {
                    $('#customer, #salechannel').prop('disabled', true);
                }

                // 關閉懸浮視窗
                setShipmentModal.hide();

                // 新增一個物流
                function createNewShip(s) {
                    let $newCart = $cartClone.clone();
                    $newCart.addClass(`-detail-${EVENT_CLASS[s.category]}`);
                    $newCart.find('.card-header strong').text(s.group_name);
                    $newCart.attr('id', `${s.category}_${s.group_id}`);
                    $newCart.find('input[name="dividend_id[]"]').val(`${s.category}_${s.group_id}`);
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
                            cloneElem.find('div[data-td="subtotal"]').text(`$${formatNumber(p.total)}`);
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
                    bindPointUseBtn();
                }
            }


            /*** fn ***/
            // #清空商品 Modal
            function resetAddProductModal() {
                $('#addProduct .-searchBar input').val('');
                $('#addProduct tbody.-appendClone.--product').empty();
                $('#addProduct #pageSum').text('');
                $('#addProduct .page-item:not(:first-child, :last-child)').remove();
                $('#addProduct nav').hide();
                $('#addProduct .-checkedNum').text(`已添加 ${Object.keys(myProductList).length} 件商品`);
                $('.-emptyData').hide();
            }
            // #清空物流 Modal
            function resetSetShipmentModal() {
                $('#setShipment blockquote h6, #setShipment figcaption').text('');
                $('#setShipment fieldset > div').empty();
                $('#setShipment .alert-danger').prop('hidden', true);
                $('#setShipment .btn-ok').prop('disabled', false);
                // console.log(myCart);
            }

            // #寫回 myProductList, myCart: 數量 qty、小計 total
            function setMyQty($qty) {
                const sid = Number($qty.closest('tr.-cloneElem').find('input[name="product_style_id[]"]').val());
                const qty = Number($qty.val());
                // myProductList
                myProductList[sid].qty = qty;
                myProductList[sid].total = myProductList[sid].price * qty;
                // HTML
                $qty.closest('tr.-cloneElem').find('div[data-td="subtotal"]').text(
                `$${formatNumber(myProductList[sid].total)}`);
                // myCart
                const ship_type = $qty.closest('tr.-cloneElem').find('input[name="shipment_type[]"]').val();
                const ship_id = $qty.closest('tr.-cloneElem').find('input[name="shipment_event_id[]"]').val();
                const shipKey = `${ship_type}_${ship_id}`;
                myCart[shipKey].total = calc_ProductTotalBySid(myCart[shipKey].products);
            }

            // #bind 計數器按鈕
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

                    setMyQty($qty);
                    // 檢查試算
                    calcAndCheckAllOrder();
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

                        setMyQty($this);
                        // 檢查試算
                        calcAndCheckAllOrder();
                    });
            }

            /*** 優惠 fn ***/
            // #檢查優惠
            function check_AllDiscount() {
                // 清空使用優惠
                resetDiscountData();

                // loop 所有優惠
                for (const type of DISC_PRIORITY) {
                    const tempDis = DiscountData[type];
                    if (tempDis && Object.keys(tempDis).length) {
                        switch (type) {
                            case 'code':
                            case 'coupon':
                                // 使用優惠券/代碼 (只會有一個)
                                const dis = tempDis;
                                const useDis = calc_OneDiscountUse(dis);
                                if (useDis) {
                                    setDiscountToMyData(dis, useDis);
                                }
                                break;

                            default:
                                // 若全館優惠(type===global)同時存在多個，擇取最優惠擇一個計算(cash|percent)
                                let bestDiscount = null;
                                let bestUse = {
                                    total: 0
                                };
                                for (const d_id in tempDis) {
                                    if (Object.hasOwnProperty.call(tempDis, d_id)) {
                                        // 使用單一優惠
                                        const dis = tempDis[d_id];
                                        const useDis = calc_OneDiscountUse(dis);

                                        /** 條件
                                         * 1. useDis !== false
                                         * 2-1. type !== global
                                         * 2-2. dis.method_code === coupon
                                         */
                                        if (useDis) {
                                            if (type !== 'global' || dis.method_code === 'coupon') {
                                                setDiscountToMyData(dis, useDis);
                                            } else if (useDis.total > bestUse.total) {
                                                bestDiscount = dis;
                                                bestUse = useDis;
                                            }
                                        }
                                    }
                                }
                                if (bestDiscount) {
                                    setDiscountToMyData(bestDiscount, bestUse);
                                }
                                break;
                        }
                    }
                }

                // 未達使用條件之優惠 HTML
                notMeetDiscount.forEach(notMeet => {
                    if (notMeet.pids.length > 0) {
                        notMeet.pids.forEach(pid => {
                            appendDiscountHtmlByPid(pid, notMeet.note, '-', false);
                        });
                    } else {
                        appendDiscountHtmlByPid('all', notMeet.note, '-', false);
                    }
                });

                // 寫入 myData (含HTML)
                function setDiscountToMyData(dis, useDis) {
                    const note = discountNote(dis);

                    // 寫入 myProductList
                    for (const sid in useDis.prod_list) {
                        if (Object.hasOwnProperty.call(useDis.prod_list, sid)) {
                            const price = useDis.prod_list[sid];
                            myProductList[sid].discount[useDis.did] = price;
                            setOneMyProdDisTotal(sid);
                            // HTML
                            appendDiscountHtmlBySid(sid, note, price, true);
                        }
                    }
                    // 寫入 myCart
                    setAllMyCartDisTotal();
                    // 寫入 myDiscount
                    myDiscount[useDis.did] = {
                        id: dis.id,
                        name: dis.title,
                        type: dis.category_code,
                        method: dis.method_code,
                        note: note,
                        total: useDis.total
                    };
                    if (dis.category_code === 'code') {
                        myDiscount[useDis.did].code = dis.sn;
                    }
                    if (dis.method_code === 'coupon') {
                        myDiscount[useDis.did].coupon = dis.coupon_title;
                    }
                }
            }

            // #計算單一優惠使用
            // return {did:'優惠類型_優惠ID', total:優惠折抵總金額/優惠券名稱, prod_list: {sid: 折扣金額}}
            function calc_OneDiscountUse(dis) {
                const did = `${dis.category_code}_${dis.id}`;
                // 0. 檢查使用條件
                if (dis.is_global === 0 && dis.product_ids.length === 0) {
                    return false; // 無可使用商品
                }
                if (dis.category_code === 'code' && dis.max_usage !== 0 && dis.usage_count >= dis.max_usage) {
                    return false; // 已達使用上限
                }
                const pids = dis.product_ids || [];
                // 1. 折扣後小計
                const original_total = calc_ProductDisedTotalByPid(pids);
                if (original_total < dis.min_consume) {
                    notMeetDiscount.push({
                        note: discountNote(dis),
                        pids
                    });
                    return false; // 不滿低消
                }
                // 2. 折抵金額、折扣比例
                let discount_total = 0, // 折抵總額
                    discount_ratio = 0; // 折扣比例
                switch (dis.method_code) {
                    case 'cash':
                        if (dis.is_grand_total) { // 累計
                            discount_total = (Math.floor(original_total / dis.min_consume)) * dis.discount_value;
                        } else {
                            discount_total = dis.discount_value;
                        }
                        discount_ratio = discount_total / original_total;
                        break;
                    case 'percent':
                        discount_ratio = (100 - dis.discount_value) / 100;
                        discount_total = Math.ceil(original_total * discount_ratio);
                        break;

                    default:
                        discount_total = dis.coupon_title || dis.method_title;
                        return {
                            did,
                            total: discount_total
                        };
                }
                // 3. 各商品折抵
                let prod_list = {}; // 優惠使用
                let last_sid = '', // 最後一個商品
                    difference = discount_total; // 誤差值
                for (const sid in myProductList) {
                    if (Object.hasOwnProperty.call(myProductList, sid)) {
                        const prod = myProductList[sid];
                        if (pids.length === 0 || pids.indexOf(prod.pid) >= 0) {
                            const price = Math.round(prod.dised_total * discount_ratio);
                            prod_list[sid] = price;
                            difference -= price;
                            last_sid = sid;
                        }
                    }
                }
                if (difference !== 0) { // 差價後補
                    prod_list[last_sid] += difference;
                }

                return {
                    did,
                    total: discount_total,
                    prod_list
                };
            }

            // #寫回 myProductList單一商品: 優惠總金額 dis_total、折後小計 dised_total
            function setOneMyProdDisTotal(sid) {
                let dis_total = 0;
                for (const key in myProductList[sid].discount) {
                    if (Object.hasOwnProperty.call(myProductList[sid].discount, key)) {
                        dis_total += myProductList[sid].discount[key];
                    }
                }
                myProductList[sid].dis_total = dis_total;
                myProductList[sid].dised_total = myProductList[sid].total - dis_total;
            }
            // #寫回 myCart所有: 優惠總金額 dis_total、折後小計 dised_total
            function setAllMyCartDisTotal() {
                for (const key in myCart) {
                    if (Object.hasOwnProperty.call(myCart, key)) {
                        let dis_total = 0;
                        myCart[key].products.forEach(sid => {
                            if (myProductList[sid]) {
                                dis_total += myProductList[sid].dis_total;
                            }
                        });
                        myCart[key].dis_total = dis_total;
                        myCart[key].dised_total = myCart[key].total - dis_total - myCart[key].point;
                    }
                }
            }

            // #清空優惠 myProductList, myCart, myDiscount, notMeetDiscount
            function resetDiscountData() {
                // myProductList
                for (const sid in myProductList) {
                    if (Object.hasOwnProperty.call(myProductList, sid)) {
                        myProductList[sid].discount = {};
                        myProductList[sid].dis_total = 0;
                        myProductList[sid].dised_total = myProductList[sid].total;
                    }
                }
                // myCart
                for (const key in myCart) {
                    if (Object.hasOwnProperty.call(myCart, key)) {
                        myCart[key].dis_total = 0;
                        myCart[key].dised_total = myCart[key].total - myCart[key].point;
                    }
                }
                // myDiscount
                myDiscount = {};
                // notMeetDiscount
                notMeetDiscount = [];

                // HTML
                $('.-cloneElem.--selectedP .-dis-data').remove();
                $('#Discount_overview table tbody').empty();
                $('#Discount_overview').prop('hidden', true);
                $('div[data-td="subtotal"]').removeClass('text-decoration-line-through');
            }

            // #優惠文字
            // ex. 消費滿 $100 折 $10（不得折抵運費），可累計折扣
            // ex. 消費滿 $100 享 88 折優惠（不含運費）
            // ex. 消費不限金額享 88 折優惠（不含運費）
            // ex. 消費滿 $100 送優惠券
            function discountNote(dis) {
                let note = '';

                // 使用優惠券
                if (dis.category_code === 'code') {
                    note += `【${dis.title}】`;
                }
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
                    note += '，可累計折扣';
                }
                return note;
            }

            // #產生單一商品優惠HTML
            // meet: booling 是否達成優惠條件
            function appendDiscountHtmlBySid(sid, note, price, meet = true) {
                const $tr = $(`tr.-cloneElem.--selectedP:has(input[name="product_style_id[]"][value="${sid}"])`);
                appendDiscountHtml($tr, note, price, meet, myProductList[sid].dised_total);
            }

            function appendDiscountHtmlByPid(pid, note, price, meet = true) {
                let $tr = null;
                if (pid === 'all') {
                    $tr = $(`tr.-cloneElem.--selectedP`);
                } else {
                    $tr = $(`tr.-cloneElem.--selectedP:has(input[name="product_id[]"][value="${pid}"])`);
                }
                appendDiscountHtml($tr, note, price, meet);
            }

            function appendDiscountHtml($tr, note, price, meet, dised_total = null) {
                let disprice = '';
                if (meet) {
                    // 折扣價
                    $tr.find('div[data-td="subtotal"]').addClass('text-decoration-line-through');
                    $tr.find('div[data-td="disedprice"]').remove();
                    $tr.find('div[data-td="subtotal"]').parent('td').append(createDispriceDiv(dised_total));
                    // 折扣金額
                    disprice = `<span class="text-danger fw-bold">折扣 $${formatNumber(price)}</span>`;
                }
                $tr.find('div[data-td="title"]').parent('td').append(createDiscountDiv(note));

                // 優惠內容
                function createDiscountDiv(note) {
                    return `<div data-td="discount" class="lh-1 small text-secondary -dis-data">
                        <span class="badge rounded-pill bg-${meet ? 'danger' : 'secondary'} fw-normal me-2">
                            ${meet ? '已達優惠' : '未達優惠'}</span>
                        ${note}
                        ${disprice}
                    </div>`;
                }
                // 折扣價
                function createDispriceDiv(dised) {
                    if (dised === null) {
                        return '';
                    }
                    return `<div data-td="disedprice" class="lh-1 text-danger fw-bold -dis-data">
                        $${formatNumber(dised)}</div>`;
                }
            }

            // #產生優惠折扣總覽HTML
            function appendDiscountOverview() {
                let overviewList = [];
                $('#Discount_overview table tbody').empty();

                for (const did in myDiscount) {
                    if (Object.hasOwnProperty.call(myDiscount, did)) {
                        const dis = myDiscount[did];
                        overviewList.push(createDiscountTr(dis));
                    }
                }
                const points = calc_AllUsePoint();
                if (points > 0) {
                    overviewList.push(createDiscountTr({
                        total: points,
                        type: 'bonus',
                        id: ''
                    }));
                }

                if (overviewList.length) {
                    $('#Discount_overview').prop('hidden', false);
                    $('#Discount_overview table tbody').append(overviewList);
                } else {
                    $('#Discount_overview').prop('hidden', true);
                }

                function createDiscountTr(dis) {
                    let className = '',
                        total = '',
                        title = '',
                        note = '';
                    if (isFinite(dis.total)) {
                        className = 'text-danger';
                        total = '- $' + dis.total;
                    } else {
                        className = 'text-primary';
                        total = '【' + dis.total + '】';
                    }
                    switch (dis.type) {
                        case 'code':
                            title = '使用優惠券';
                            break;
                        case 'bonus':
                            title = '鴻利點數折抵';
                            break;

                        default:
                            title = dis.name;
                            break;
                    }
                    if (dis.note) {
                        note = `<span class="small text-secondary">－${dis.note}</span>`;
                    }

                    return `<tr data-id="${dis.id}">
                        <td class="col-8">${title}${note}</td>
                        <td class="text-end pe-4 ${className}">${total}</td>
                    </tr>`;
                }
            }

            // 優惠使用（二擇一）
            $('input[name="coupon_type"]').off('change').on('change', function() {
                const type = $('input[name="coupon_type"]:checked').val();
                $('div.--ctype').prop('hidden', true);
                $('div.--ctype select, div.--ctype input').prop('disabled', true);
                $(`div.--ctype.-${type}`).prop('hidden', false);
                $(`div.--ctype.-${type} select, div.--ctype.-${type} input`).prop('disabled', false);

                switch (type) {
                    case 'coupon': // 優惠券
                        resetCouponCodeData();
                        getCouponsAPI();
                        break;
                    case 'code': // 優惠代碼
                        resetCouponCouponData();
                        break;
                }
                // 檢查試算
                calcAndCheckAllOrder();
            })

            /*** 優惠代碼 fn ***/
            // #優惠券代碼 -coupon_sn
            $('button.-coupon_sn').off('click').on('click', function() {
                $('div.--ctype.-code input[name="coupon_sn"]').val('');
                DiscountData.code = {};
                // call API
                getCouponCheckAPI($('input.-coupon_sn').val());
            });
            // #檢查優惠代碼API
            function getCouponCheckAPI(sn) {
                const _URL = @json(route('api.cms.discount.check-discount-code'));
                let Data = {
                    sn,
                    product_id: []
                };
                // init Html
                $('.d-flex.-coupon_sn, input.-coupon_sn').removeClass('is-valid is-invalid');
                $('.-feedback.-coupon_sn').removeClass('valid-feedback invalid-feedback').prop('hidden', true);

                // Data - sn
                if (!Data.sn) {
                    toast.show('請輸入優惠代碼', {
                        type: 'danger'
                    });
                    return false;
                }
                // Data - product_id
                $('input[name="product_id[]"]').each(function(index, element) {
                    // element == this
                    if ((Data.product_id).indexOf($(element).val()) < 0) {
                        (Data.product_id).push($(element).val());
                    }
                });
                if (Data.product_id.length <= 0) {
                    toast.show('請先加入商品', {
                        type: 'danger'
                    });
                    return false;
                }
                Data.product_id = (Data.product_id).toString();

                axios.post(_URL, Data)
                    .then((result) => {
                        const res = result.data;
                        console.log('檢查優惠代碼API', res);
                        let valid_cls = '',
                            msg = '';
                        if (res.status === '0') {
                            const dis = res.data;
                            DiscountData.code = dis;
                            // 紀錄sn
                            $('div.--ctype.-code input[name="coupon_sn"]').val(Data.sn);

                            valid_cls = 'valid';
                            msg = `使用優惠券${discountNote(dis)}`;
                        } else {
                            // 清空
                            $('div.--ctype.-code input[name="coupon_sn"]').val('');
                            DiscountData.code = {};

                            valid_cls = 'invalid';
                            msg = res.message;
                        }

                        // 檢查試算
                        calcAndCheckAllOrder();

                        $('.d-flex.-coupon_sn, input.-coupon_sn').addClass(`is-${valid_cls}`);
                        $('.-feedback.-coupon_sn').addClass(`${valid_cls}-feedback`)
                            .prop('hidden', false).text(msg);
                    }).catch((err) => {
                        // 清空優惠代碼
                        resetCouponCodeData();
                        console.error(err);
                    });
            }

            // #清空優惠代碼
            function resetCouponCodeData() {
                // Data
                DiscountData.code = {};
                // Html
                $('div.--ctype.-code input').val('');
                $('div.-feedback.-coupon_sn').empty();
                $('div.--ctype.-code .-coupon_sn').removeClass('is-valid is-invalid valid-feedback invalid-feedback');
            }

            /*** 優惠券 fn ***/
            // #優惠券 -coupon
            $('div.--ctype.-coupon select[name="coupon_sn"]').off('change').on('change', function() {
                const id = $(this).val();
                DiscountData.coupon = UserCoupons[id];
                $('div.--ctype.-coupon .-note').text('優惠內容：' + discountNote(UserCoupons[id]));

                // 檢查試算
                calcAndCheckAllOrder();
            });
            // #取得持有優惠券API
            function getCouponsAPI() {
                const _URL = @json(route('api.cms.discount.get-coupons'));
                const Data = {
                    customer_id: $('#customer').val()
                };
                // init
                UserCoupons = {};
                $('div.--ctype.-coupon select[name="coupon_sn"], div.--ctype.-coupon .-note').empty();
                $('div.--ctype.-coupon select[name="coupon_sn"]').append(
                    '<option value="" selected>請選擇優惠券</option>'
                );

                axios.post(_URL, Data)
                    .then((result) => {
                        const res = result.data;
                        console.log('持有優惠券', res);
                        if (res.status === '0') {
                            const coupons = res.data;
                            coupons.forEach(c => {
                                $('div.--ctype.-coupon select[name="coupon_sn"]').append(
                                    `<option value="${c.id}" title="${discountNote(c)}">${c.title}</option>`
                                );
                                UserCoupons[c.id] = c;
                            });
                        }
                    }).catch((err) => {
                        console.error(err);
                    });
            }

            // #清空優惠券
            function resetCouponCouponData() {
                // Data
                DiscountData.coupon = {};
                UserCoupons = {};
                // Html
                $('div.--ctype.-coupon select[name="coupon_sn"]').val('');
                $('div.--ctype.-coupon select[name="coupon_sn"], div.--ctype.-coupon .-note').empty();
                $('div.--ctype.-coupon select[name="coupon_sn"]').append(
                    '<option value="" selected>請選擇優惠券</option>'
                );
            }

            /*** 鴻利 fn -bonus_point ***/
            $('#customer').off('change.point').on('change.point', function() {
                getPointsAPI();
            });

            // #取得持有鴻利API
            function getPointsAPI() {
                const _URL = @json(route('api.cms.discount.get-dividend-point'));
                const Data = {
                    customer_id: $('#customer').val()
                };

                // init
                UserPoints = 0;
                $('.-hasPoints').text(UserPoints);
                $('input.-bonus_point, input[name="dividend[]"]').val('');
                $('.d-flex.-bonus_point, input.-bonus_point').removeClass(`is-invalid is-valid`);

                if (!Data.customer_id) {
                    toast.show('請先選擇訂購客戶。', {
                        type: 'warning',
                        title: '目前鴻利點數'
                    });
                    return false;
                }

                axios.post(_URL, Data)
                    .then((result) => {
                        const res = result.data;
                        console.log('持有鴻利', res);
                        if (res.status === '0') {
                            UserPoints = res.data || 0;
                            $('.-hasPoints').text(UserPoints);
                            $cartClone.find('.-hasPoints').text(UserPoints);
                            $('input.-bonus_point').prop('max', UserPoints);
                        }
                    }).catch((err) => {
                        console.log('取得鴻利錯誤', err);
                    });
            }

            // #計算可用鴻利上限總計
            function calc_maxPoint(ship_key) {
                let max = 0;

                myCart[ship_key].products.forEach(sid => {
                    if (myProductList[sid]) {
                        max += myProductList[sid].point * myProductList[sid].qty;
                    }
                });

                return max;
            }

            // #設定鴻利上限總計
            function setMaxPoint(ship_key) {
                const max = calc_maxPoint(ship_key);

                $(`#${ship_key} .-maxPoints`).text(max);
                $(`#${ship_key} input.-bonus_point`).prop('max', max > UserPoints ? UserPoints : max);
            }

            // bind 使用鴻利按鈕 -bonus_point
            function bindPointUseBtn() {
                $('button.-bonus_point').off('click').on('click', function() {
                    const id = $(this).closest('.-detail').attr('id');
                    check_BonusUse(id);
                    // 檢查運費
                    check_AllDlvFee();
                    // 應付金額 HTML
                    calc_set_AllAmount();

                    const sum = calc_AllUsePoint();
                    toast.show(`總共已使用 ${sum} 點鴻利點數`);
                });
            }

            // 檢查鴻利使用
            function check_BonusUse(ship_key) {
                const $bonus = $(`#${ship_key} input.-bonus_point`);
                let bonus = Number($bonus.val());
                const max = calc_maxPoint(ship_key);
                const totalUse = calc_AllUsePoint() + bonus;
                let valid_cls = '';
                $(`#${ship_key} input.-bonus_point`).removeClass('is-invalid is-valid');

                if (bonus > UserPoints) {
                    valid_cls = 'invalid';
                    bonus = 0;
                    $bonus.val(0);
                    toast.show('超過目前持有鴻利', {
                        type: 'danger'
                    });
                } else if (totalUse > UserPoints) {
                    valid_cls = 'invalid';
                    bonus = 0;
                    $bonus.val(0);
                    toast.show('總使用點數超過目前持有鴻利', {
                        type: 'danger'
                    });
                } else if (bonus > max) {
                    valid_cls = 'invalid';
                    bonus = 0;
                    $bonus.val(0);
                    toast.show('超過該子訂單使用上限', {
                        type: 'danger'
                    });
                } else if (bonus >= 0) {
                    valid_cls = 'valid';
                }
                // 紀錄點數
                $(`#${ship_key} div.-bonus_point input[name="dividend[]"]`).val(bonus);
                myCart[ship_key].point = bonus;
                myCart[ship_key].dised_total = myCart[ship_key].total - myCart[ship_key].dis_total - bonus;

                if (valid_cls === 'invalid' || (valid_cls === 'valid' && bonus > 0)) {
                    $(`#${ship_key} input.-bonus_point`).addClass(`is-${valid_cls}`);
                }

                return valid_cls === 'valid' ? bonus : 0;
            }

            // 計算總使用鴻利
            function calc_AllUsePoint() {
                let total_use = 0;

                for (const key in myCart) {
                    if (Object.hasOwnProperty.call(myCart, key)) {
                        total_use += myCart[key].point;
                    }
                }

                return total_use;
            }

            /*** 運費 fn ***/
            // #檢查運費
            function check_AllDlvFee() {
                // 清空運費
                resetDlvFeeData();

                // loop 所有物流
                for (const key in myCart) {
                    if (Object.hasOwnProperty.call(myCart, key)) {
                        // 寫入 myCart
                        myCart[key].dlv_fee = calc_OneDlvFeeByShipKey(key);
                        // HTML
                        if (myCart[key].dlv_fee > 0) {
                            $(`#${key}.-detail div[data-td="dlv_fee"]`).prev('div').append(
                                `<span class="lh-1 small text-secondary ms-2 -dlv-data">
                                    ${deliverNote(myCart[key].rules)}</span>`
                            );
                            $(`#${key}.-detail div[data-td="dlv_fee"]`).text(`$${formatNumber(myCart[key].dlv_fee)}`);
                        } else {
                            $(`#${key}.-detail div[data-td="dlv_fee"]`).text('免運');
                        }
                    }
                }
            }

            // #計算運費dlv_fee by ship_key
            function calc_OneDlvFeeByShipKey(ship_key) {
                let dlv_fee = 0;
                switch (myCart[ship_key].type) {
                    case 'deliver':
                        const total = myCart[ship_key].dised_total;
                        for (const rule of myCart[ship_key].rules) {
                            if ((rule.is_above === 'false' && total >= rule.min_price && total < rule.max_price) ||
                                (rule.is_above === 'true' && total >= rule.max_price)) {
                                dlv_fee = Number(rule.dlv_fee);
                                break;
                            }
                        }
                        break;

                    case 'pickup':
                    default:
                        dlv_fee = 0;
                        break;
                }
                return dlv_fee;
            }

            // #清空運費 myCart
            function resetDlvFeeData() {
                // myCart
                for (const key in myCart) {
                    if (Object.hasOwnProperty.call(myCart, key)) {
                        myCart[key].dlv_fee = 0;
                    }
                }
                // HTML
                $('.-detail .-dlv-data').remove();
                $('.-detail div[data-td="dlv_fee"]').text('-');
            }

            // #運費說明文字
            function deliverNote(rules) {
                let note = '';
                for (const rule of rules) {
                    if (rule.is_above === 'true') {
                        note = '消費滿 $' + formatNumber(rule.max_price) + '免運';
                        break;
                    }
                }
                return note;
            }

            /*** 計算 fn ***/
            // #計算商品total by sid
            function calc_ProductTotalBySid(sids = []) {
                let total = 0;
                for (const sid in myProductList) {
                    if (Object.hasOwnProperty.call(myProductList, sid) &&
                        (sids.length === 0 || sids.indexOf(Number(sid)) >= 0)) {
                        total += myProductList[sid].total;
                    }
                }
                return total;
            }

            // #計算商品總折扣後小計 by pid
            function calc_ProductDisedTotalByPid(pids = []) {
                let dised_total = 0;
                for (const sid in myProductList) {
                    if (Object.hasOwnProperty.call(myProductList, sid) &&
                        (pids.length === 0 || pids.indexOf(myProductList[sid].pid) >= 0)) {
                        dised_total += myProductList[sid].dised_total;
                    }
                }
                return dised_total;
            }

            // #計算 應付金額
            function calc_set_AllAmount() {
                // 總商品小計
                let all_total = 0;
                // 總折扣
                let all_discount = 0;
                // 總運費
                let all_dlvFee = 0;
                // 總金額
                let all_sum = 0;

                for (const key in myCart) {
                    if (Object.hasOwnProperty.call(myCart, key)) {
                        const cart = myCart[key];
                        all_total += cart.total;
                        all_discount += cart.dis_total + cart.point;
                        all_dlvFee += cart.dlv_fee;
                    }
                }
                all_sum = all_total - all_discount + all_dlvFee;

                // HTML
                $('#Total_price td[data-td="subtotal"]').text(`$${formatNumber(all_total)}`);
                $('#Total_price td[data-td="discount"]').text(`- $${formatNumber(all_discount)}`);
                $('#Total_price td[data-td="dlv_fee"]').text(`$${formatNumber(all_dlvFee)}`);
                $('#Total_price td[data-td="sum"]').text(`$${formatNumber(all_sum)}`);
                appendDiscountOverview();

                return {
                    all_total,
                    all_discount,
                    all_dlvFee,
                    all_sum
                };
            }

            /*** 試算訂單 ***/
            function calcAndCheckAllOrder() {
                // 檢查優惠
                check_AllDiscount();

                for (const key in myCart) {
                    if (Object.hasOwnProperty.call(myCart, key)) {
                        // 鴻利上限
                        setMaxPoint(key);
                        // 檢查鴻利
                        check_BonusUse(key);
                    }
                }

                // 檢查運費
                check_AllDlvFee();
                // 應付金額 HTML
                calc_set_AllAmount();
            }
        </script>
        <script>
            // 預設地址資料
            const DefaultAddress = {
                name: @json($defaultAddress->name ?? ''),
                city_id: @json($defaultAddress->city_id ?? ''),
                region_id: @json($defaultAddress->region_id ?? ''),
                addr: @json($defaultAddress->addr ?? ''),
                regions: @json($default_region)
            };

            /*** 步驟 ***/
            // 無商品不可下一步
            if (!$('.-cloneElem.--selectedP').length) {
                $('#STEP_1 .-next_step').prop('disabled', true);
                $('#customer, #salechannel').prop('disabled', false);
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

            /*** 第二步 ***/
            // 預設地址 radio
            $('input[name="ord_radio"]').off('change').on('change', function () {
                if ($(this).val() === 'default') {
                    $('input[name="ord_name"]').val(DefaultAddress.name);
                    $('input[name="ord_addr"]').val(DefaultAddress.addr);
                    $('select[name="ord_city_id"]').val(DefaultAddress.city_id);
                    getRegionsAction(
                        $(`select[name="ord_region_id`),
                        DefaultAddress.city_id,
                        DefaultAddress.region_id
                    );
                    setSameDefault($('#rec_same'));
                    setSameDefault($('#sed_same'));
                } else {
                    // 清空
                    $(`input[name="ord_name"],
                       input[name="ord_addr"],
                       select[name="ord_city_id"],
                       select[name="ord_region_id`).val('');
                    $(`select[name="ord_region_id"]`).html('<option value="">地區</option>');
                    setSameNew($('#rec_same'));
                    setSameNew($('#sed_same'));
                }

                function setSameDefault($target) {
                    const prefix_ = $target.attr('id').replace(/same/g, '');
                    if ($target.prop('checked')) {
                        $(`input[name="${prefix_}name"]`).val(DefaultAddress.name);
                        $(`input[name="${prefix_}addr"]`).val(DefaultAddress.addr);
                        $(`select[name="${prefix_}city_id"]`).val(DefaultAddress.city_id);
                        getRegionsAction(
                            $(`select[name="${prefix_}region_id"]`),
                            DefaultAddress.city_id,
                            DefaultAddress.region_id
                        );
                    }
                }
                function setSameNew($target) {
                    const prefix_ = $target.attr('id').replace(/same/g, '');
                    if ($target.prop('checked')) {
                        // 清空
                        $(`input[name="${prefix_}name"],
                        input[name="${prefix_}addr"],
                        select[name="${prefix_}city_id"],
                        select[name="${prefix_}region_id`).val('');
                        $(`select[name="${prefix_}region_id"]`).html('<option value="">地區</option>');
                    }
                }
            });

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
