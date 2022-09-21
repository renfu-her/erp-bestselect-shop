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
                <div class="card-body px-4 py-0">
                    <div class="table-responsive tableOverBox">
                        <table class="table tableList table-sm table-hover mb-0">
                            <tbody>
                                <tr>
                                    <td>物流</td>
                                    <td>
                                        <select name="ship_category[]" class="form-select -sx" required>
                                            @foreach ($shipmentCategory as $key => $value)
                                                <option value="{{ $value->code }}"
                                                    @if ($subOrder->ship_category == $value->code) selected @endif>
                                                    {{ $value->category }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="ship_event_id[]" class="form-select  -sx">
                                            @foreach ($shipEvent[$subOrder->ship_category] as $key => $value)
                                                <option value="{{ $value->id }}"
                                                    @if ($subOrder->ship_event_id == $value->id) selected @endif>{{ $value->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input class="form-control form-control-sm -sx" name="dlv_fee[]" type="number"
                                            aria-label="運費" value="{{ $subOrder->dlv_fee }}" required>
                                    </td>

                                </tr>
                                <tr>
                                    <td>銷貨備註</td>
                                    <td colspan="3"> <input class="form-control form-control-sm -sx" name="sub_order_note[]" aria-label="運費"
                                            value="{{ $subOrder->note }}"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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
        </script>
    @endpush
@endonce
