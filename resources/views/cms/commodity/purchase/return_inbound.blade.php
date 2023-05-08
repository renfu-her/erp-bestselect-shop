@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">#{{ $return->sn }} 退出入庫審核</h2>

    @if($errors->any())
        <div class="alert alert-danger mt-3">{!! implode('', $errors->all('<div>:message</div>')) !!}</div>
    @endif

    <form method="post" action="{{ $form_action }}" class="-banRedo">
        @csrf
        <div class="card shadow p-4 mb-4">
            <h6>商品列表</h6>
            <div class="table-responsive tableOverBox">
                <table id="Pord_list" class="table table-striped tableList">
                    <thead>
                        <tr>
                            <th style="width:3rem;">#</th>
                            <th>商品名稱</th>
                            <th>SKU</th>
                            <th>採購數量</th>
                            <th class="text-center" style="width: 10%">入庫數量</th>
                            <th class="text-center" style="width: 10%">退出數量</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($return_main_item as $key => $data)
                            <tr class="--prod">
                                <th scope="row">{{ $key + 1 }}</th>
                                <td><span class="badge rounded-pill bg-success">一般</span>{{ $data->product_title }}</td>
                                <td>{{ $data->sku }}</td>
                                <td>{{ number_format($data->p_num) }}</td>
                                <td>
                                    <input type="text" value="{{ number_format($data->inbound_num) }}" name="qty_actual[]" class="form-control form-control-sm text-center" readonly disabled>
                                </td>
                                <td>
                                    <input type="text" value="{{ $data->num ?? 0 }}" name="total_return_qty" class="form-control form-control-sm text-center" readonly disabled>
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
                                                <td class="text-center" style="width: 10%">入庫數量</td>
                                                <td class="text-center" style="width: 10%">已退數量</td>
                                                <td class="text-center" style="width: 10%">退出數量</td>
                                                <td>入庫備註</td>
                                            </tr>
                                        </thead>

                                        <tbody class="border-top-0 -appendClone --selectedIB">
                                            @foreach ($data->inbound as $i_value)
                                                <tr class="-cloneElem --selectedIB">
                                                    <td class="text-center">
                                                        <button type="button" class="icon icon-btn -del fs-5 text-danger rounded-circle border-0"><i class="bi bi-trash"></i></button>
                                                        <input type="hidden" name="inbound_id[]" value="{{ $i_value->inbound_id }}">
                                                        <input type="hidden" name="purchase_id[]" value="{{ $i_value->event_id }}">
                                                        <input type="hidden" name="purchase_item_id[]" value="{{ $data->purchase_item_id }}">
                                                        <input type="hidden" name="return_item_id[]" value="{{ $data->id }}">
                                                        <input type="hidden" name="product_style_id[]" value="{{ $data->product_style_id }}">
                                                        <input type="hidden" name="product_title[]" value="{{ $data->product_title }}">
                                                        <input type="hidden" name="real_rq[]" value="{{ $data->num ?? 0 }}">
                                                        <input type="hidden" name="sub_rq[]" value="0">
                                                    </td>

                                                    <td data-td="sn">{{ $i_value->inbound_sn }}</td>
                                                    <td data-td="depot">{{ $i_value->depot_name }}</td>
                                                    <td data-td="expiry">{{ date('Y/m/d', strtotime($i_value->expiry_date)) }}</td>

                                                    <td class="text-center">
                                                        <input type="text" name="qty[]" value="{{ $i_value->inbound_num }}" class="form-control form-control-sm text-center" readonly disabled>
                                                    </td>
                                                    <td class="text-center">{{ $i_value->inbound_return_num ?? 0 }}</td>

                                                    <td class="text-center">
                                                        <input type="number" name="return_qty[]" value="{{ 0 }}"
                                                            max="{{ (($i_value->inbound_num - $i_value->shipped_num) < 0) ? 0 : ($i_value->inbound_num - $i_value->shipped_num) }}"
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
                    </tbody>
                </table>
            </div>
            {{--
            @error('error_msg')
                <div class="alert alert-danger" role="alert">
                    {{ $message }}
                </div>
            @enderror
            --}}
        </div>

        <div id="submitDiv">
            <div class="col-auto">
                @if(! $return->inbound_date && count($return_main_item) > 0)
                    <button type="submit" class="btn btn-primary px-4">送出</button>
                @endif

                <a href="{{ route('cms.purchase.return_list', ['purchase_id' => $return->purchase_id ]) }}" class="btn btn-outline-primary px-4" role="button">返回退出列表</a>
                <a href="{{ route('cms.purchase.return_detail', ['return_id' => $return->id]) }}" class="btn btn-outline-primary px-4" role="button">返回明細</a>
            </div>
        </div>
    </form>
@endsection

@once
    @push('sub-scripts')
        <script>
        $(function () {
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
            $('tr.-cloneElem.--selectedIB input[name="return_qty[]"]')
            .off('change')
            .on('change', checkBackQtySum);

            $('form.-banRedo').off('submit.check').on('submit.check', function () {
                return checkBackQtySum();
            });

            // 加總入庫數量
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

            // check 退出數量 === sum(退回數量)
            function checkBackQtySum() {
                let chk = true;
                $('#Pord_list tbody tr.--prod').each(function (index, element) {
                    // element == this
                    const total_return_qty = Number($(element).find('input[name="total_return_qty"]').val()) || 0;
                    let return_qty = 0;
                    $(element).next('tr.--rece').find('input[name="return_qty[]"]').each(function (i, el) {
                        return_qty += Number($(el).val()) || 0;
                    });
                    if (total_return_qty !== return_qty) {
                        chk &= false;
                        $(element).next('tr.--rece').find('input[name="return_qty[]"]').addClass('is-invalid');
                    } else {
                        $(element).next('tr.--rece').find('input[name="return_qty[]"]').removeClass('is-invalid');
                    }
                    $(element).next('tr.--rece').find('input[name="sub_rq[]"]').val(return_qty);
                });
                $('#submitDiv button:submit').prop('disabled', !chk);
                return chk;
            }
        });
        </script>
    @endpush
@endonce
