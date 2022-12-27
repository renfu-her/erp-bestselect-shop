@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">#{{ $breadcrumb_data['sn'] }} 退貨入庫審核</h2>
    @if ($event === 'consignment')
        <x-b-consign-navi :id="$delivery->event_id"></x-b-consign-navi>
    @endif
    @if ($event === 'csn_order')
        <x-b-csnorder-navi :id="$delivery->event_id"></x-b-csnorder-navi>
    @endif
    @if($errors->any())
        <div class="alert alert-danger mt-3">{!! implode('', $errors->all('<div>:message</div>')) !!}</div>
    @endif

    <form method="post" action="{{ $formAction }}" class="-banRedo">
        @method('POST')
        @csrf
        <div class="card shadow p-4 mb-4">
            <h6>商品列表</h6>
            <input type="hidden" name="event" value="{{$event ?? null}}">
            <input type="hidden" name="depot_id" value="{{$depot_id ?? null}}">
            <div class="table-responsive tableOverBox">
                <table id="Pord_list" class="table table-striped tableList">
                    <thead>
                        <tr>
                            <th style="width:3rem;">#</th>
                            <th>商品名稱</th>
                            <th>SKU</th>
                            <th>訂購數量</th>
                            <th class="text-center" style="width: 10%">出貨數量</th>
                            <th class="text-center" style="width: 10%">退貨數量</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (null != $ord_items_arr)
                            @foreach ($ord_items_arr as $key => $ord)
                                <tr class="--prod">
                                    <th scope="row">{{ $key + 1 }}</th>
                                    <td>
                                        @if ($ord->combo_product_title)
                                            <span class="badge rounded-pill bg-warning text-dark">組合包</span> [
                                        @else
                                            <span class="badge rounded-pill bg-success">一般</span>
                                        @endif
                                        {{ $ord->product_title }} @if($ord->combo_product_title) ] {{$ord->combo_product_title}} @endif
                                    </td>
                                    <td>{{ $ord->sku }}</td>
                                    <td data-td="o_qty">{{ number_format($ord->qty) }}</td>
                                    <td>
                                        <input type="text" value="" name="qty_actual[]" class="form-control form-control-sm text-center" readonly>
                                    </td>
                                    <td>
                                        <input type="text" value="{{$ord->total_to_back_qty ?? 0}}" name="total_back_qty" class="form-control form-control-sm text-center" readonly>
                                    </td>
                                </tr>
                                <tr class="--rece">
                                    <td></td>
                                    <td colspan="7" class="pt-0 ps-0">
                                        <table class="table mb-0 table-sm table-hover border-start border-end">
                                            <thead>
                                            <tr class="border-top-0" style="border-bottom-color:var(--bs-secondary);">
                                                <td class="text-center">刪除</td>
                                                <td>入庫單</td>
                                                <td>倉庫</td>
                                                <td>效期</td>
                                                <td class="text-center" style="width: 10%">出貨數量</td>
                                                <td class="text-center" style="width: 10%">已退數量</td>
                                                <td class="text-center" style="width: 10%">退回數量</td>
                                                <td>入庫說明</td>
                                            </tr>
                                            </thead>
                                            <tbody class="border-top-0 -appendClone --selectedIB">
                                            @foreach ($ord->receive_depot as $rec)
                                                <tr class="-cloneElem --selectedIB">
                                                    <td class="text-center">
                                                        <button type="button"
                                                                @if (isset($delivery->audit_date)) @else disabled @endif
                                                                class="icon icon-btn -del fs-5 text-danger rounded-circle border-0">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                        <input type="hidden" name="id[]" value="{{ $rec->id }}">
                                                        <input type="hidden" value="{{$ord->total_to_back_qty}}" name="total_to_back_qty[]" class="form-control form-control-sm text-center" readonly>
                                                        <input type="hidden" name="backed_qty[]" value="{{ $rec->back_qty }}">
                                                    </td>
                                                    <td data-td="sn">{{ $rec->inbound_sn }}</td>
                                                    <td data-td="depot">{{ $rec->depot_name }}</td>
                                                    <td data-td="expiry">{{ date('Y/m/d', strtotime($rec->expiry_date)) }}</td>
                                                    <td class="text-center">
                                                        <input type="text" name="qty[]" value="{{ $rec->qty }}" class="form-control form-control-sm text-center" readonly>
                                                    </td>
                                                    <td class="text-center" data-td="backed_qty">{{ $rec->back_qty ?? 0 }}</td>
                                                    <td class="text-center">
                                                        <input type="number" name="back_qty[]" value="{{ $rec->elebac_qty ?? 0 }}"
                                                            max="{{ ($rec->qty - ($rec->back_qty ?? 0)) }}"
                                                            min="1"
                                                            class="form-control form-control-sm text-center">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="memo[]" value="" class="form-control form-control-sm">
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            @error('error_msg')
                <div class="alert alert-danger" role="alert">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <div id="submitDiv">
            <div class="col-auto">
                @if(false == isset($bacPapa->inbound_date))
                    <button type="submit" class="btn btn-primary px-4">送出</button>
                @endif
                <a href="{{ Route('cms.delivery.back_list', ['deliveryId' => $delivery->id ]) }}" class="btn btn-outline-primary px-4" role="button">返回退貨列表</a>
                @if($delivery->event == App\Enums\Delivery\Event::order()->value)
                    <a href="{{ Route('cms.order.detail', ['id' => $order_id, 'subOrderId' => $eventId ]) }}" class="btn btn-outline-primary px-4" role="button">返回明細</a>
                @elseif($delivery->event == App\Enums\Delivery\Event::consignment()->value)
                    <a href="{{ Route('cms.consignment.edit', ['id' => $eventId ]) }}" class="btn btn-outline-primary px-4" role="button">返回明細</a>
                @elseif($delivery->event == App\Enums\Delivery\Event::csn_order()->value)
                    <a href="{{ Route('cms.consignment-order.edit', ['id' => $eventId ]) }}" class="btn btn-outline-primary px-4" role="button">返回明細</a>
                @endif
            </div>
        </div>
    </form>

@endsection
@once
    @push('sub-scripts')
        <script>
        $(function () {
            // const Readonly = @json(isset($delivery->audit_date));

            // init
            sumExportQty();
            checkBackQtySum();

            // 刪除
            $('tr.-cloneElem.--selectedIB .-del').off('click').on('click', function () {
                $(this).closest('tr.-cloneElem.--selectedIB').remove();
                sumExportQty();
                checkBackQtySum();
            });
            // 改退回數量
            $('tr.-cloneElem.--selectedIB input[name="back_qty[]"]')
            .off('change')
            .on('change', checkBackQtySum);

            $('form.-banRedo').off('submit.check').on('submit.check', function () {
                return checkBackQtySum();
            });

            // 加總出貨數量
            function sumExportQty() {
                $('#Pord_list tbody tr.--prod').each(function (index, element) {
                    // element == this
                    let sum = 0;
                    $(element).next('tr.--rece').find('input[name="qty[]"]').each(function (i, el) {
                        sum += Number($(el).val()) || 0;
                    });
                    $(element).find('input[name="qty_actual[]"]').val(sum);
                });
            }

            // check 退貨數量 === sum(退回數量)
            function checkBackQtySum() {
                let chk = true;
                $('#Pord_list tbody tr.--prod').each(function (index, element) {
                    // element == this
                    const total_back_qty = Number($(element).find('input[name="total_back_qty"]').val()) || 0;
                    let back_qty = 0;
                    $(element).next('tr.--rece').find('input[name="back_qty[]"]').each(function (i, el) {
                        back_qty += Number($(el).val()) || 0;
                    });
                    if (total_back_qty !== back_qty) {
                        chk &= false;
                        $(element).next('tr.--rece').find('input[name="back_qty[]"]').addClass('is-invalid');
                    } else {
                        $(element).next('tr.--rece').find('input[name="back_qty[]"]').removeClass('is-invalid');
                    }
                });
                $('#submitDiv button:submit').prop('disabled', !chk);
                return chk;
            }
        });
        </script>
    @endpush
@endonce
