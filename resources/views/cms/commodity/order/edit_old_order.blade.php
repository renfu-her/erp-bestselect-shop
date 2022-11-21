@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">#{{ $order->sn }} 編輯訂單</h2>

    <form action="{{ route('cms.order.edit-item', ['id' => $order->id]) }}" method="post">
        @csrf
        @foreach ($subOrders as $subOrder)
            <input type="hidden" name="sub_order_id[]" value="{{ $subOrder->id }}">
            <div @class([
                'card shadow mb-4 -detail',
                '-detail-primary' => $subOrder->ship_category === 'deliver',
                '-detail-warning' => $subOrder->ship_category === 'pickup',
            ])>
                <div
                    class="card-header px-4 d-flex align-items-center bg-white flex-wrap justify-content-end border-bottom-0">
                    <strong class="flex-grow-1 mb-0">#{{ $subOrder->sn }}</strong>
                    <strong class="mb-0 mx-2">{{ $subOrder->ship_event }}</strong>
                    <span class="badge -badge fs-6">{{ $subOrder->ship_category_name }}</span>
                </div>
                <div class="card-body px-4 py-0">
                    <div class="table-responsive tableOverBox">
                        <table class="table tableList table-sm table-hover mb-0">
                            <thead class="table-light text-secondary">
                                <tr>
                                    <th scope="col">商品名稱-款式</th>
                                    <th scope="col">SKU</th>
                                    <th>售價</th>
                                    <th>經銷價</th>

                                    <th>數量</th>
                                    <th>說明</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($subOrder->items as $key => $item)
                                    <input type="hidden" name="item_id[]" value="{{ $item->item_id }}">
                                    <tr>
                                        <td>{{ $item->product_title }}
                                            <input type="hidden" name="style_id[]" value="{{ $item->style_id }}">
                                        </td>
                                        <td>{{ $item->sku }}</td>
                                        <td>
                                            <input class="form-control form-control-sm -sx" type="text" aria-label="售價"
                                                value="{{ $item->price }}" disabled>
                                        </td>
                                        <td>
                                            <input class="form-control form-control-sm -sx" type="text" aria-label="經銷價"
                                                value="{{ $item->dealer_price }}" disabled>
                                        </td>

                                        <td class="text-center">
                                            <input class="form-control form-control-sm -sx" type="text" aria-label="數量"
                                                value="{{ $item->qty }}" disabled>
                                        </td>
                                        <td>
                                            <input class="form-control form-control-sm -l" type="text" name="note[]"
                                                aria-label="說明" value="{{ $item->note }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($canEdit)
                    <div class="card-header px-4 text-secondary border-top border-bottom-0">物流資訊</div>
                    <div class="card-body px-4 py-0">
                        <div class="table-responsive tableOverBox">
                            <table class="table tableList table-sm table-hover mb-0">
                                <tbody>
                                    <tr>
                                        <td>物流</td>
                                        <td>
                                            <select name="ship_category[]" class="form-select form-select-sm -sx w-100"
                                                required>
                                                @foreach ($shipmentCategory as $key => $value)
                                                    <option value="{{ $value->code }}"
                                                        @if ($subOrder->ship_category == $value->code) selected @endif>
                                                        {{ $value->category }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="ship_event_id[]"
                                                class="-select2 -single form-select form-select-sm -sx">
                                                @foreach ($shipEvent[$subOrder->ship_category] as $key => $value)
                                                    <option value="{{ $value->id }}"
                                                        @if ($subOrder->ship_event_id == $value->id) selected @endif>
                                                        {{ $value->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>運費</td>
                                        <td style="width: 20%;">
                                            <div class="input-group input-group-sm flex-nowrap">
                                                <span class="input-group-text">$</span>
                                                <input class="form-control -sx" name="dlv_fee[]" type="number"
                                                    aria-label="運費" value="{{ $subOrder->dlv_fee }}" required>
                                            </div>
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
                <div class="card-header px-4  text-secondary border-top border-bottom-0">銷貨備註</div>
                <div class="card-body px-4 py-2">
                    <input class="form-control form-control-sm -sx" name="sub_order_note[]" aria-label="運費"
                        value="{{ $subOrder->note }}">
                </div>
            </div>
        @endforeach

        <div class="card shadow p-4 mb-4">
            @php
                $prefix = [
                    'orderer' => 'ord',
                    'receiver' => 'rec',
                    'sender' => 'sed',
                ];
                
                $addr_title = [
                    'orderer' => '購買人',
                    'receiver' => '收件人',
                    'sender' => '寄件人',
                ];
            @endphp
            @foreach ($addr as $key => $_addr)
                <input type="hidden" name="{{ $prefix[$key] }}_id" value="{{ $_addr->id }}">
                <h6 class="mb-2">{{ $addr_title[$key] }}</h6>
                <div class="row">
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">姓名 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" value="{{ $_addr->name }}"
                            name="{{ $prefix[$key] }}_name" placeholder="請輸入購買人姓名" required>
                    </div>
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">電話 <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" value="{{ $_addr->phone }}"
                            name="{{ $prefix[$key] }}_phone" placeholder="請輸入購買人電話" required>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">地址 <span class="text-danger">*</span></label>
                        <input type="hidden" name="ord_address">
                        <div class="input-group has-validation">
                            <select name="{{ $prefix[$key] }}_city_id" class="form-select" style="max-width:20%">
                                <option value="">縣市</option>
                                @foreach ($citys as $value)
                                    <option value="{{ $value['city_id'] }}"
                                        @if ($_addr->city_id == $value['city_id']) selected @endif>{{ $value['city_title'] }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="{{ $prefix[$key] }}_region_id" class="form-select" style="max-width:20%">
                                <option value="">地區</option>
                                @foreach ($_addr->default_region as $value)
                                    <option value="{{ $value['region_id'] }}"
                                        @if ($_addr->region_id == $value['region_id']) selected @endif>
                                        {{ $value['region_title'] }}
                                    </option>
                                @endforeach
                            </select>
                            <input name="{{ $prefix[$key] }}_addr" type="text" class="form-control"
                                placeholder="請輸入購買人地址" value="{{ $_addr->addr }} " required>
                            <button class="btn btn-outline-success -format_addr_btn" type="button">格式化</button>
                            <div class="invalid-feedback">
                                @error('record')
                                    {{ $message }}
                                    {{-- 地址錯誤訊息: ord_city_id, ord_region_id, ord_addr --}}
                                @enderror
                                @error($prefix[$key] . '_address')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach


            <div class="row">
                <div class="col-12 mb-3">
                    <label class="form-label mt-3">備註</label>
                    <textarea name="order_note" class="form-control" rows="3">{{ $order->note }}</textarea>
                </div>
            </div>
        </div>

        <div id="Invoice" class="card shadow p-4 mb-4">
            {{-- 發票種類 --}}
            <div class="row">
                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">發票種類 <span class="text-danger">*</span></legend>
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="category" value="B2C" type="radio" required
                                    {{ old('category', $order->category ?? 'B2C') == 'B2C' ? 'checked' : '' }}>
                                個人
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="category" value="B2B" type="radio" required
                                    {{ old('category', $order->category) == 'B2B' ? 'checked' : '' }}>
                                公司．企業
                            </label>
                        </div>
                    </div>
                    <div class="invalid-feedback">
                        @error('category')
                            {{ $message }}
                        @enderror
                    </div>
                </fieldset>
            </div>

            {{-- 發票方式 --}}
            <fieldset class="col-12 col-sm-6 mb-3">
                <legend class="col-form-label p-0 mb-2">發票方式<span class="text-danger">*</span></legend>
                <div class="px-1 pt-1">
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input class="form-check-input" name="invoice_method" value="e_inv" type="radio" required
                                {{ old('invoice_method', $order->invoice_category) == '電子發票' ? 'checked' : '' }}>
                            電子發票
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input class="form-check-input" name="invoice_method" value="print" type="radio" required
                                {{ old('invoice_method', $order->invoice_category) == '紙本發票' ? 'checked' : '' }}>
                            列印紙本發票
                        </label>
                    </div>

                </div>
                <div class="invalid-feedback">
                    @error('invoice_method')
                        {{ $message }}
                    @enderror
                </div>
            </fieldset>
            {{-- 發票方式: 列印紙本發票 --}}
            <div class="row inv_method_print d-none">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">發票抬頭</label>
                    <input type="text" name="inv_title" class="form-control @error('inv_title') is-invalid @enderror"
                        placeholder="請輸入發票抬頭" aria-label="發票抬頭" value="{{ old('inv_title', $order->inv_title) }}"
                        disabled>
                    <div class="invalid-feedback">
                        @error('inv_title')
                            {{ $message }}
                        @enderror
                    </div>
                </div>
                <div class="col-12 col-sm-6 mb-3 category_b2b d-none">
                    <label class="form-label">統一編號</label>
                    <input type="text" name="buyer_ubn" class="form-control @error('buyer_ubn') is-invalid @enderror"
                        placeholder="請輸入統一編號" aria-label="統一編號" value="{{ old('buyer_ubn', $order->buyer_ubn) }}"
                        disabled>
                    <div class="invalid-feedback">
                        @error('buyer_ubn')
                            {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>
            {{-- 發票方式: 電子發票 - 載具類型 --}}
            <fieldset class="col-12 mb-1 inv_method_carrier">
                <legend class="col-form-label p-0 mb-2">載具類型 {{  old('carrier_type') }}<span class="text-danger">*</span></legend>
                <div class="px-1 pt-1">
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input class="form-check-input" name="carrier_type" value="2" type="radio"
                                {{ old('carrier_type', $order->order_carrier_type) == 2 ? 'checked' : '' }}>
                            會員電子發票
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input class="form-check-input" name="carrier_type" value="0" type="radio"
                                {{ old('carrier_type', $order->order_carrier_type) == 0 ? 'checked' : '' }}>
                            手機條碼載具
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input class="form-check-input" name="carrier_type" value="1" type="radio"
                                {{ old('carrier_type', $order->order_carrier_type) == 1 ? 'checked' : '' }}>
                            自然人憑證條碼載具
                        </label>
                    </div>
                </div>
                <div class="invalid-feedback">
                    @error('carrier_type')
                        {{ $message }}
                    @enderror
                </div>
            </fieldset>

            <div class="row">
                {{-- 電子發票: 會員電子發票 --}}
                <div class="col-12 col-sm-6 mb-3 buyer_email">
                    <label class="form-label l_buyer_email">買受人E-mail</label>
                    <input type="text" name="buyer_email" class="form-control @error('buyer_email') is-invalid @enderror"
                        placeholder="請輸入買受人E-mail" aria-label="買受人E-mail" value="{{ old('buyer_email', $order->buyer_email ?? '') }}">
                    <mark class="fw-light small">
                        <i class="bi bi-exclamation-diamond-fill mx-2 text-warning"></i>發票開立時寄送的通知信收件位置
                    </mark>

                    <div class="invalid-feedback">
                        @error('buyer_email')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
                {{-- 電子發票: 條碼載具 --}}
                <div class="col-12 col-sm-6 mb-3 inv_method_carrier">
                    <label class="form-label l_carrier_num">載具號碼 <span class="text-danger">*</span></label>
                    <input type="text" name="carrier_num" class="form-control @error('carrier_num') is-invalid @enderror"
                        placeholder="請輸入載具號碼" aria-label="載具號碼" value="{{ old('carrier_num', $order->carrier_num ?? '') }}">
                    <div class="invalid-feedback">
                        @error('carrier_num')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>
        </div>


        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">送出</button>
            <a href="{{ Route('cms.order.detail', ['id' => $order->id]) }}" class="btn btn-outline-primary px-4"
                role="button">返回明細</a>
        </div>
    </form>
@endsection

@once
    @push('sub-styles')
        <link rel="stylesheet" href="{{ Asset('dist/css/order.css') }}">
    @endpush
    @push('sub-scripts')
        <script>
            let shipEvent = @json($shipEvent);

            $('select[name="ship_category[]"]').change(function() {
                // console.log(shipEvent[$(this).val()]);
                let shipEventEle = $('select[name="ship_event_id[]"]', $(this).parent().parent());

                shipEventEle.html(shipEvent[$(this).val()].map(function(n) {
                    return "<option value='" + n['id'] + "'>" + n['name'] + "</option>";
                }));

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


            //發票種類
            $('input[type=radio][name="category"]').on('click change', function() {

                switch (this.value) {
                    case 'B2B': // 公司
                        $('.inv_method_print, .inv_method_print .category_b2b').removeClass('d-none');
                        $('.inv_method_print input[type=text]').prop({
                            disabled: false
                        });

                        $('input:radio[name=invoice_method][value="print"]').click();
                        $('input:radio[name=invoice_method][value="e_inv"]').prop({
                            disabled: true,
                        });
                        break;

                    case 'B2C': // 個人
                    default:
                        $('.inv_method_print, .inv_method_print .category_b2b').addClass('d-none');
                        $('.inv_method_print input[type=text]').prop({
                            disabled: true
                        });

                        $('input:radio[name=invoice_method][value="e_inv"]').prop({
                            disabled: false,
                        });
                        $('input:radio[name=invoice_method][value="e_inv"]').click();
                        break;
                }
            });

            //發票方式
            $('input[type=radio][name=invoice_method]').on('click change', function() {
                switch (this.value) {
                    case 'give': // 捐贈
                        //-捐贈
                        $('.inv_method_give').removeClass('d-none');
                        $('select[name=love_code]').prop({
                            disabled: false,
                            required: true
                        });
                        //-列印
                        $('.inv_method_print').addClass('d-none');
                        $('.inv_method_print input').prop({
                            disabled: true
                        });
                        //-載具
                        $('.inv_method_carrier').addClass('d-none');
                        $('.inv_method_carrier input').prop({
                            disabled: true,
                            required: false,
                            checked: false
                        });
                        break;

                    case 'print': // 印出
                        //-捐贈
                        $('.inv_method_give').addClass('d-none');
                        $('#love_code').prop({
                            disabled: true,
                            required: false
                        });
                        //-列印
                        $('.inv_method_print').removeClass('d-none');
                        $('.inv_method_print input').prop({
                            disabled: false
                        });
                        //-載具
                        $('.inv_method_carrier').addClass('d-none');
                        $('.inv_method_carrier input').prop({
                            disabled: true,
                            required: false,
                            checked: false
                        });
                        break;

                    case 'e_inv': // 載具
                    default:
                        //-捐贈
                        $('.inv_method_give').addClass('d-none');
                        $('#love_code').prop({
                            disabled: true,
                            required: false
                        }).val('');
                        //-列印
                        $('.inv_method_print').addClass('d-none');
                        $('.inv_method_print input').prop({
                            disabled: true
                        });
                        //-載具
                        $('.inv_method_carrier').removeClass('d-none');
                        $('.inv_method_carrier input').prop({
                            disabled: false,
                            required: true
                        });
                        $('.inv_method_carrier input[name="carrier_type"][value="2"]').click();
                        break;
                        break;
                }
            });

            //載具類型
            $('input[type=radio][name=carrier_type]').on('click change', function() {
                $('.inv_method_carrier input[name="carrier_num"]').val('');
                switch (this.value) {
                    case '2': // 會員電子發票
                        // -手機/自然人
                        $('.carrier_0').addClass('d-none');
                        $('.carrier_0 input').prop({
                            disabled: true,
                            required: false
                        });
                        $('.inv_method_carrier input[name="carrier_num"]').val($('.buyer_email input').val());
                        break;

                    case '0': // 手機條碼載具
                    case '1': // 自然人憑證條碼載具
                    default:
                        // -手機/自然人
                        $('.carrier_0').removeClass('d-none');
                        $('.carrier_0 input').prop({
                            disabled: false,
                            required: true
                        });
                        break;
                }
            });

            $(function() {
                $_category = $('input[type=radio][name="category"]:checked').val();
                switch ($_category) {
                    case 'B2B': // 公司
                        $('.inv_method_print, .inv_method_print .category_b2b').removeClass('d-none');
                        $('.inv_method_print input[type=text]').prop({
                            disabled: false
                        });

                        // $('input:radio[name=invoice_method][value="print"]').click();
                        $('input:radio[name=invoice_method][value="e_inv"]').prop({
                            disabled: true,
                        });
                        $('.inv_method_carrier').addClass('d-none');

                        break;

                    case 'B2C': // 個人
                    default:
                        $('.inv_method_print, .inv_method_print .category_b2b').addClass('d-none');
                        $('.inv_method_print input[type=text]').prop({
                            disabled: true
                        });
                        /*
                        $('input:radio[name=invoice_method][value="e_inv"]').prop({
                            disabled: false,
                        });
                        $('input:radio[name=invoice_method][value="e_inv"]').click();
                        */
                        $_invoice_method = $('input[type=radio][name="invoice_method"]:checked').val();

                        switch ($_invoice_method) {

                            case 'print': // 印出
                                //-捐贈
                                $('.inv_method_give').addClass('d-none');
                                $('#love_code').prop({
                                    disabled: true,
                                    required: false
                                });
                                //-列印
                                $('.inv_method_print').removeClass('d-none');
                                $('.inv_method_print input').prop({
                                    disabled: false
                                });
                                //-載具

                                $('.inv_method_carrier').addClass('d-none');
                                $('.inv_method_carrier input').prop({
                                    disabled: true,
                                    required: false,
                                    checked: false
                                });

                                break;

                            case 'e_inv': // 載具
                            default:
                                $_carrier_type = $('input[type=radio][name="carrier_type"]:checked').val();
                                console.log($_carrier_type);

                                if ($_carrier_type == 0) {
                                    $('.carrier_0').removeClass('d-none');
                                    $('.carrier_0 input').prop({
                                        disabled: false,
                                        required: true
                                    });

                                    $('.carrier_2').addClass('d-none');

                                }

                                break;
                        }

                        break;
                }

            })
        </script>
    @endpush
@endonce
