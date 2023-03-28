@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">#{{ $breadcrumb_data['sn'] }} 退貨審核</h2>
    @if ($event === 'consignment')
        <x-b-consign-navi :id="$delivery->event_id"></x-b-consign-navi>
    @endif
    @if ($event === 'csn_order')
        <x-b-csnorder-navi :id="$delivery->event_id"></x-b-csnorder-navi>
    @endif
    @error('error_msg')
    <div class="alert alert-danger" role="alert">
        {{ $message }}
    </div>
    @enderror
    @error('item_error')
    <div class="alert alert-danger" role="alert">
        {{ $message }}
    </div>
    @enderror

    <form id="form1" method="post" action="{{ $formAction }}" class="-banRedo">
        @method('POST')
        @csrf
        <div class="card shadow p-4 mb-4">
            <h6>訂單退貨單內容</h6>
            <div class="col-12">
                <label class="form-label">退貨單備註</label>
                <input class="form-control" type="text" value="{{$bacPapa->memo ?? ''}}" name="dlv_memo" placeholder="退貨單備註">
            </div>
            <div class="table-responsive tableOverBox mb-3">
                <table id="Pord_list" class="table table-striped tableList">
                    <thead class="small">
                        <tr>
                            <th style="width:3rem;">#</th>
                            <th class="text-center">退貨</th>
                            <th>商品名稱</th>
                            <th>SKU</th>
                            <th>退款金額</th>
                            <th>扣除獎金</th>
                            <th>扣除購物金</th>
                            <th>原數量</th>
                            <th class="text-center" style="width: 10%">欲退數量</th>
                            <th>說明</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (null != $ord_items)
                            @foreach ($ord_items as $key => $item)
                                <tr class="--prod">
                                    <th scope="row">{{ $key + 1 }}
                                        <input type="hidden" name="id[]" value="{{ $item->id ?? '' }}" />
                                        <input type="hidden" name="event_item_id[]" value="{{ $item->event_item_id ?? '' }}" />
                                        <input type="hidden" name="product_style_id[]" value="{{ $item->product_style_id ?? '' }}" />
                                        <input type="hidden" name="sku[]" value="{{ $item->sku ?? '' }}" />
                                        <input type="hidden" name="origin_qty[]" value="{{ $item->origin_qty ?? '' }}" />
                                    </th>
                                    <td class="text-center">
                                        <input type="hidden" name="show[]" value="{{$item->show ?? 0}}">
                                        <input type="checkbox"  class="form-check-input -show"
                                            @if(1 == ($item->show ?? 0)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="text" value="{{ $item->product_title ?? '' }}" name="product_title[]"
                                            class="form-control form-control-sm -l" required>
                                    </td>
                                    <td>{{ $item->sku ?? '' }}</td>
                                    <td>
                                        <input type="number" value="{{ $item->price ?? '' }}" name="price[]"
                                            class="form-control form-control-sm -sm" min="0" step="0.01" required>
                                    </td>
                                    <td>
                                        <input type="number" value="{{ $item->bonus ?? '' }}" name="bonus[]"
                                               class="form-control form-control-sm -sm" min="0" step="0.01" required>
                                    </td>
                                    <td>
                                        <input type="number" value="{{ $item->dividend ?? 0 }}" name="dividend[]"
                                               class="form-control form-control-sm -sm" min="0" step="1" required>
                                    </td>
                                    <td class="text-center">{{ $item->origin_qty ? number_format($item->origin_qty) : 0 }}</td>
                                    @if('create' == $method || isset($bacPapa) && null == $bacPapa->inbound_date)
                                        <td>
                                            <x-b-qty-adjuster name="back_qty[]" value="{{ $item->back_qty ?? 0 }}"
                                                              min="0" max="{{ $item->origin_qty ?? '' }}"
                                                              size="sm" minus="-" plus="+"></x-b-qty-adjuster>
                                        </td>
                                    @else
                                        <td class="text-center">
                                            <input type="hidden" name="back_qty[]" value="{{ $item->back_qty ?? 0 }}" />
                                            {{ $item->back_qty ? number_format($item->back_qty) : 0 }}
                                        </td>
                                    @endif
                                    <td>
                                        <input type="text" value="{{ $item->memo ?? '' }}" name="memo[]" class="form-control form-control-sm -xl">
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            <h6 class="mb-1">其他項目</h6>

            <div class="table-responsive tableOverBox">
                <table class="table table-sm table-hover tableList mb-1">
                    <thead class="small">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">會計科目</th>
                        <th scope="col">項目</th>
                        <th scope="col">金額（單價）</th>
                        <th scope="col">數量</th>
                        <th scope="col">備註</th>
                    </tr>
                    </thead>

                    <tbody>
                    @php
                        $items = $dlv_other_items;
                    @endphp

                    @for ($i = 0; $i < 5; $i++)
                        <tr>
                            <td>{{ $i + 1 }}<input type="hidden" name="back_item_id[{{ $i }}]" value="{{ $items[$i]->id ?? '' }}" class="d-target"></td>

                            <td>
                                <select class="select-check form-select form-select-sm -select2 -single @error('bgrade_id.' . $i) is-invalid @enderror" name="bgrade_id[{{ $i }}]" data-placeholder="請選擇會計科目">
                                    <option value="" selected disabled>請選擇會計科目</option>
                                    @foreach($total_grades as $g_value)
                                        <option value="{{ $g_value['primary_id'] }}" {{ $g_value['primary_id'] == old('bgrade_id.' . $i, $items[$i]->grade_id ?? '') ? 'selected' : '' }}
                                            @if($g_value['grade_num'] === 1)
                                                class="grade_1"
                                            @elseif($g_value['grade_num'] === 2)
                                                class="grade_2"
                                            @elseif($g_value['grade_num'] === 3)
                                                class="grade_3"
                                            @elseif($g_value['grade_num'] === 4)
                                                class="grade_4"
                                            @endif
                                        >{{ $g_value['code'] . ' ' . $g_value['name'] }}</option>
                                    @endforeach
                                </select>
                            </td>

                            <td>
                                <input type="text" name="btitle[{{ $i }}]"
                                        value="{{ old('btitle.' . $i, $items[$i]->product_title ?? '') }}"
                                        class="d-target form-control form-control-sm @error('btitle.' . $i) is-invalid @enderror"
                                        aria-label="項目" placeholder="請輸入項目" disabled>
                            </td>

                            <td>
                                <input type="number" name="bprice[{{ $i }}]"
                                        value="{{ old('bprice.' . $i, $items[$i]->price ?? '') }}"
                                        class="d-target r-target form-control form-control-sm @error('bprice.' . $i) is-invalid @enderror"
                                        aria-label="金額" placeholder="請輸入金額" disabled>
                            </td>

                            <td>
                                <input type="number" name="bqty[{{ $i }}]"
                                        value="{{ old('bqty.' . $i, $items[$i]->qty ?? '') }}" min="0"
                                        class="d-target r-target form-control form-control-sm @error('bqty.' . $i) is-invalid @enderror"
                                        aria-label="數量" placeholder="請輸入數量" disabled>
                            </td>

                            <td>
                                <input type="text" name="bmemo[{{ $i }}]"
                                        value="{{ old('bmemo.' . $i, $items[$i]->memo ?? '') }}"
                                        class="d-target form-control form-control-sm @error('bmemo.' . $i) is-invalid @enderror"
                                        aria-label="備註" placeholder="請輸入備註" disabled>
                            </td>
                        </tr>
                    @endfor
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
                <input type="hidden" name="method" value="{{ $method }}" />
                <button type="submit" class="btn btn-primary px-4" >送出</button>
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
            // +/- btn
            $('button.-minus, button.-plus').on('click', function() {
                const $input = $(this).siblings('input[type="number"]');
                const max = $input.attr('max') !== '' ? Number($input.attr('max')) : null;
                const min = $input.attr('min') !== '' ? Number($input.attr('min')) : null;
                const m_qty = Number($input.val());
                if ($(this).hasClass('-minus') && (min !== null && m_qty > min)) {
                    $input.val(m_qty - 1);
                }
                if ($(this).hasClass('-plus') && (max != null && m_qty < max)) {
                    $input.val(m_qty + 1);
                }
            });
            $(document).on('change', 'select.select-check', function() {
                if(this.value){
                    $(this).parents('tr').find('.d-target').prop('disabled', false);
                    $(this).parents('tr').find('.r-target').prop('required', true);
                } else {
                    $(this).parents('tr').find('.d-target').prop('disabled', true);
                    $(this).parents('tr').find('.r-target').prop('required', false);
                }
            });

            $.each($('select.select-check'), function(i, ele) {
                if(ele.value){
                    $(ele).parents('tr').find('.d-target').prop('disabled', false);
                    $(ele).parents('tr').find('.r-target').prop('required', true);
                } else {
                    $(ele).parents('tr').find('.d-target').prop('disabled', true);
                    $(ele).parents('tr').find('.r-target').prop('required', false);
                }
            });

            // 退貨 checkbox
            $('input.-show').each(function (index, element) {
                // element == this
                const $back_qty = $(element).closest('tr').find('[name="back_qty[]"]');
                const checked = $(element).prop('checked');
                $back_qty.data('max', $back_qty.attr('max'));
                if (checked) {
                    $back_qty.attr({min: 1, max: $back_qty.data('max')});
                } else {
                    $back_qty.attr({min: 0, max: 0});
                }
            });
            $('input.-show').on('change', function () {
                const $this = $(this);
                const checked = $this.prop('checked');
                if (checked) {
                    $this.prev('[name="show[]"]').val(1);
                    const $back_qty = $this.closest('tr').find('[name="back_qty[]"]');
                    $back_qty.attr({min: 1, max: $back_qty.data('max')}).val(1);
                } else {
                    $this.prev('[name="show[]"]').val(0);
                    $this.closest('tr').find('[name="back_qty[]"]')
                    .attr({min: 0, max: 0}).val(0);
                }
            });
        </script>
    @endpush
@endonce
