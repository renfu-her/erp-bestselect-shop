@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">修改摘要/稅別</h2>

    <form method="POST" action="{{ $form_action }}">
        @csrf
        <div class="card shadow p-4 mb-4">
            <div class="table-responsive tableOverBox">
                <table class="table table-sm table-hover tableList mb-1">
                    <thead class="small">
                        <tr>
                            <th scope="col" class="text-center">#</th>
                            <th scope="col">會計科目<span class="text-danger">*</span></th>
                            <th scope="col">摘要說明</th>
                            <th scope="col" class="text-end">數量</th>
                            <th scope="col" class="text-end">金額</th>
                            <th scope="col">稅別</th>
                            <th scope="col">備註</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php
                            $serial = 1;
                            $product_grade_f = true;
                        @endphp

                        @foreach($received_data as $value)
                            <tr class="bg-info">
                                <td class="text-center">{{ $serial }}</td>
                                <td>
                                    <select class="form-select form-select-sm -select2 -single" name="received[{{ $value->received_id }}][grade_id]" data-placeholder="請選擇會計科目" required>
                                        <option value="" selected disabled>請選擇</option>
                                        @foreach($total_grades as $g_value)
                                            <option value="{{ $g_value['primary_id'] }}"{{ $g_value['primary_id'] == $value->all_grades_id ? 'selected' : '' }}
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
                                    <input class="form-control form-control-sm -l" name="received[{{ $value->received_id }}][summary]" 
                                        type="text" value="{{ $value->summary }}">
                                </td>
                                <td class="text-end">1</td>
                                <td class="text-end">{{ number_format($value->tw_price, 2) }}</td>
                                <td>
                                    <div class="form-check form-check-inline lh-base">
                                        <label class="form-check-label" for="tax_{{ $serial }}_1">
                                            <input class="form-check-input" 
                                                name="received[{{ $value->received_id }}][taxation]" 
                                                value="1" type="radio" id="tax_{{ $serial }}_1" 
                                                required @if ($value->taxation == '1') checked @endif>
                                            應稅
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline lh-base">
                                        <label class="form-check-label" for="tax_{{ $serial }}_2">
                                            <input class="form-check-input" 
                                                name="received[{{ $value->received_id }}][taxation]" 
                                                value="0" type="radio" id="tax_{{ $serial }}_2" 
                                                required @if ($value->taxation == '0') checked @endif>
                                            免稅
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <input class="form-control form-control-sm -l" name="received[{{ $value->received_id }}][note]" 
                                        type="text" value="{{ $value->note }}">
                                </td>
                            </tr>
                            @php
                                $serial++;
                            @endphp
                        @endforeach

                        @foreach($order_list_data as $value)
                            <tr>
                                <td class="text-center">{{ $serial }}</td>
                                <td>
                                    @if($product_grade_f)
                                        <select class="form-select form-select-sm -select2 -single" name="product_grade_id" data-placeholder="請選擇會計科目" required>
                                            <option value="" selected disabled>請選擇</option>
                                            @foreach($total_grades as $g_value)
                                                <option value="{{ $g_value['primary_id'] }}"{{ $g_value['primary_id'] == $received_order->product_grade_id ? 'selected' : '' }}
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
                                        @php
                                            $product_grade_f = false;
                                        @endphp
                                    @endif
                                </td>
                                <td>{{ $value->product_title }}</td>
                                <td class="text-end">{{ $value->product_qty }}</td>
                                <td class="text-end">{{ number_format($value->product_origin_price, 2) }}</td>
                                <td>
                                    <div class="form-check form-check-inline lh-base">
                                        <label class="form-check-label" for="tax_{{ $serial }}_1">
                                            <input class="form-check-input" 
                                                name="product[{{ $value->product_id }}][taxation]" 
                                                value="1" type="radio" id="tax_{{ $serial }}_1" 
                                                required @if ($value->product_taxation == '1') checked @endif>
                                            應稅
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline lh-base">
                                        <label class="form-check-label" for="tax_{{ $serial }}_2">
                                            <input class="form-check-input" 
                                                name="product[{{ $value->product_id }}][taxation]" 
                                                value="0" type="radio" id="tax_{{ $serial }}_2" 
                                                required @if ($value->product_taxation == '0') checked @endif>
                                            免稅
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    {{ $value->product_note }}
                                </td>
                            </tr>
                            @php
                                $serial++;
                            @endphp
                        @endforeach

                        @if($order->dlv_fee > 0)
                            <tr>
                                <td class="text-center">{{ $serial }}</td>
                                <td>
                                    <select class="form-select form-select-sm -select2 -single" name="logistics_grade_id" data-placeholder="請選擇會計科目" required>
                                        <option value="" selected disabled>請選擇</option>
                                        @foreach($total_grades as $g_value)
                                            <option value="{{ $g_value['primary_id'] }}"{{ $g_value['primary_id'] == $received_order->logistics_grade_id ? 'selected' : '' }}
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
                                <td>物流費用</td>
                                <td class="text-end">1</td>
                                <td class="text-end">{{ number_format($order->dlv_fee, 2) }}</td>
                                <td>
                                    <div class="form-check form-check-inline lh-base">
                                        <label class="form-check-label" for="tax_{{ $serial }}_1">
                                            <input class="form-check-input" 
                                                ame="order_dlv[{{ $order->id }}][taxation]" 
                                                value="1" type="radio" id="tax_{{ $serial }}_1" 
                                                required @if ($order->dlv_taxation == '1') checked @endif>
                                            應稅
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline lh-base">
                                        <label class="form-check-label" for="tax_{{ $serial }}_2">
                                            <input class="form-check-input" 
                                                name="order_dlv[{{ $order->id }}][taxation]" 
                                                value="0" type="radio" id="tax_{{ $serial }}_2" 
                                                required @if ($order->dlv_taxation == '0') checked @endif>
                                            免稅
                                        </label>
                                    </div>
                                </td>
                                <td></td>
                            </tr>
                            @php
                                $serial++;
                            @endphp
                        @endif

                        @if($order->discount_value > 0)
                            @foreach($order_discount ?? [] as $d_value)
                                <tr>
                                    <td class="text-center">{{ $serial }}</td>
                                    <td>
                                        <select class="form-select form-select-sm -select2 -single" name="discount[{{ $d_value->id }}][grade_id]" data-placeholder="請選擇會計科目" required>
                                            <option value="" selected disabled>請選擇</option>
                                            @foreach($total_grades as $g_value)
                                                <option value="{{ $g_value['primary_id'] }}"{{ $g_value['primary_id'] == $d_value->discount_grade_id ? 'selected' : '' }}
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
                                    <td>{{ $d_value->title }}</td>
                                    <td class="text-end">1</td>
                                    <td class="text-end">{{ number_format($d_value->discount_value, 2) }}</td>
                                    <td>
                                        <div class="form-check form-check-inline lh-base">
                                            <label class="form-check-label" for="tax_{{ $serial }}_1">
                                                <input class="form-check-input" 
                                                    name="discount[{{ $d_value->id }}][taxation]" 
                                                    value="1" type="radio" id="tax_{{ $serial }}_1" 
                                                    required @if ($d_value->discount_taxation == '1') checked @endif>
                                                應稅
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline lh-base">
                                            <label class="form-check-label" for="tax_{{ $serial }}_2">
                                                <input class="form-check-input" 
                                                    name="discount[{{ $d_value->id }}][taxation]" 
                                                    value="0" type="radio" id="tax_{{ $serial }}_2" 
                                                    required @if ($d_value->discount_taxation == '0') checked @endif>
                                                免稅
                                            </label>
                                        </div>
                                    </td>
                                    <td></td>
                                </tr>
                                @php
                                    $serial++;
                                @endphp
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">確認</button>
            <a href="{{ url()->previous() }}" class="btn btn-outline-primary px-4" role="button">
                返回上一頁
            </a>
        </div>
    </form>
@endsection

@once
@push('sub-styles')
<style>
    .grade_1 {
        padding-left: 1ch;
    }

    .grade_2 {
        padding-left: 2ch;
    }

    .grade_3 {
        padding-left: 4ch;
    }

    .grade_4 {
        padding-left: 8ch;
    }
</style>
@endpush
@push('sub-scripts')
<script>
    // 會計科目樹狀排版
    $('.-select2').select2({
        templateResult: function (data) {
            // We only really care if there is an element to pull classes from
            if (!data.element) {
                return data.text;
            }

            var $element = $(data.element);

            var $wrapper = $('<span></span>');
            $wrapper.addClass($element[0].className);

            $wrapper.text(data.text);

            return $wrapper;
        }
    });
</script>
@endpush
@endonce