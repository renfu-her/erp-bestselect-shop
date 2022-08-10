@extends('layouts.main')

@section('sub-content')
    <h2 class="mb-3">收款單入款審核</h2>

    <form method="POST" action="{{ $form_action }}">
        @csrf
        <div class="card shadow p-4 mb-4">
            <div class="row mb-3">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">收款單號：</label>
                    <span>{{$received_order->sn}}</span>
                </div>

                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">承辦者：</label>
                    <span>{{ $undertaker ? $undertaker->name : '' }}</span>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">訂單編號：</label>
                    <span>{{ $order ? $order->sn : '' }}</span>
                </div>

                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">審核日期：<span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('receipt_date') is-invalid @enderror" 
                        value="{{ old('receipt_date', $received_order->receipt_date ?? date('Y-m-d', strtotime( date('Y-m-d'))) ) }}" 
                        name="receipt_date" aria-label="審核日期">
                    <div class="invalid-feedback">
                        @error('receipt_date')
                        {{ $message }}
                        @enderror
                    </div>
                </div>

                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">發票號碼：</label>
                    <input type="text" class="form-control @error('invoice_number') is-invalid @enderror" 
                        value="{{ old('invoice_number', $received_order->invoice_number) }}" 
                        aria-label="發票號碼" name="invoice_number" />
                    <div class="invalid-feedback">
                        @error('invoice_number')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>

            <div class="table-responsive tableoverbox">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr class="small wrap">
                            <th scope="col" style="width:10%"></th>
                            <th scope="col" style="width:35%" class="text-center">借</th>
                            <th scope="col" style="width:10%" class="text-end">借方金額</th>
                            <th scope="col" style="width:35%" class="text-center">貸</th>
                            <th scope="col" style="width:10%" class="text-end">貸方金額</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td scope="row">
                                @php
                                    $total_debit_price = 0;
                                    $total_credit_price = 0;
                                @endphp
                            </td>
                            <td colspan="2" class="p-0">
                                {{-- 借方 --}}
                                <table class="table mb-0">
                                    @foreach($debit as $d_key => $d_value)
                                    <tr>
                                        <td style="width:77.77%" class="border-end">
                                            @if ($d_value->received_info)
                                                @if ($d_value->received_info->received_method == 'credit_card')
                                                    @php
                                                        $received_id = $d_value->received_info->received_id;
                                                    @endphp
                                                    <div class="col-12 mb-1">
                                                        {{ $d_key + 1 . '. ' . $d_value->method_name }}
                                                        <input type="hidden" name="credit_card[{{ $received_id }}][received_id]" value="{{ $d_value->received_info->received_id }}">
                                                        <input type="hidden" name="received_method" value="credit_card">
                                                        <input type="hidden" name="credit_card[{{ $received_id }}][received_method_id]" value="{{ $d_value->received_info->received_method_id }}">
                                                    </div>
                                                    <div class="col-12 mb-1">
                                                        <label class="form-label">信用卡號：</label>
                                                        <input type="text" class="form-control" name="credit_card[{{ $received_id }}][cardnumber]" value="{{ $d_value->received_info->credit_card_number }}" data-placeholder="信用卡號"/>
                                                    </div>
                                                    <div class="col-12 mb-1">
                                                        <label class="form-label">刷卡金額：</label>
                                                        <span>{{ number_format($d_value->received_info->credit_card_price) }}</span>
                                                    </div>
                                                    <div class="col-12 mb-1">
                                                        <label class="form-label">刷卡日期：</label>
                                                        <input type="date" class="form-control" name="credit_card[{{ $received_id }}][checkout_date]" value="{{ date('Y-m-d', strtotime($d_value->received_info->credit_card_checkout_date)) ?? date('Y-m-d', strtotime( date('Y-m-d'))) }}" data-placeholder="刷卡日期">
                                                    </div>
                                                    <div class="col-12 mb-1">
                                                        <label class="form-label">信用卡別：</label>
                                                        <select class="form-select -select2 -single" name="credit_card[{{ $received_id }}][card_type_code]" data-placeholder="請選擇信用卡別">
                                                            <option value="">請選擇</option>
                                                            @foreach($card_type as $key => $value)
                                                                <option value="{{ $key }}"{{ $key == $d_value->received_info->credit_card_type_code ? 'selected' : ''}}>{{ $value }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-12 mb-1">
                                                        <label class="form-label">持卡人：</label>
                                                        <input type="text" class="form-control" name="credit_card[{{ $received_id }}][card_owner_name]" value="{{ $d_value->received_info->credit_card_owner_name }}" data-placeholder="持卡人"/>
                                                    </div>
                                                    <div class="col-12 mb-1">
                                                        <label class="form-label">授權碼：</label>
                                                        <input type="text" class="form-control" name="credit_card[{{ $received_id }}][authcode]" value="{{ $d_value->received_info->credit_card_authcode }}" data-placeholder="授權碼">
                                                    </div>
                                                    <div class="col-12 mb-1">
                                                        <label class="form-label">會計科目：<span class="text-danger">*</span></label>
                                                        <select class="form-select -select2 -single" name="credit_card[{{ $received_id }}][all_grades_id]" data-placeholder="請選擇會計科目" required>
                                                            <option value="" selected disabled>請選擇</option>
                                                            @foreach($credit_card_grade as $value)
                                                                <option value="{{ $value['grade_id'] }}"{{ $value['grade_id'] == $d_value->received_info->all_grades_id ? 'selected' : ''}}
                                                                    @if($value['grade_num'] === 1)
                                                                        class="grade_1"
                                                                        @elseif($value['grade_num'] === 2)
                                                                        class="grade_2"
                                                                        @elseif($value['grade_num'] === 3)
                                                                        class="grade_3"
                                                                        @elseif($value['grade_num'] === 4)
                                                                        class="grade_4"
                                                                    @endif
                                                                >{{ $value['name'] }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-12 mb-1">
                                                        <label class="form-label">結帳地區：</label>
                                                        <select class="form-select -select2 -single" name="credit_card[{{ $received_id }}][checkout_area_code]" data-placeholder="請選擇信用卡別">
                                                            <option value="">請選擇</option>
                                                            @foreach($checkout_area as $key => $value)
                                                                <option value="{{ $key }}"{{ $key == $d_value->received_info->credit_card_area_code ? 'selected' : ''}}>{{ $value }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @elseif ($d_value->received_info->received_method == 'remit')
                                                    {{ $d_key + 1 . '. ' . $d_value->method_name . ' ' . $d_value->account_code . ' - ' . $d_value->account_name . ' - ' . $d_value->summary . '（' . $d_value->received_info->remit_memo . '）' }}
                                                @else
                                                    {{ $d_key + 1 . '. ' . $d_value->name }}
                                                @endif
                                            @endif
                                        </td>
                                        <td style="width:22.22%" class="text-end">
                                            @if ($d_value->price < 0)
                                                -${{ number_format(abs($d_value->price)) }}
                                            @else
                                                ${{ number_format($d_value->price) }}
                                            @endif
                                        </td>
                                    </tr>
                                    @php
                                        $total_debit_price += $d_value->price;
                                    @endphp
                                    @endforeach
                                </table>
                            </td>
                            <td colspan="2" class="p-0">
                                {{-- 貸方 --}}
                                <table class="table mb-0">
                                    @foreach($credit as $value)
                                    <tr class="border-bottom">
                                        <td style="width:77.78%" class="border-end">
                                            {{ $value->name }}
                                        </td>
                                        <td style="width:22.22%" class="text-end">
                                            @if ($value->price < 0)
                                                -${{ number_format(abs($value->price)) }}
                                            @else
                                                ${{ number_format($value->price) }}
                                            @endif
                                        </td>
                                    </tr>
                                    @php
                                        $total_credit_price += $value->price;
                                    @endphp
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td>合計</td>
                            <td></td>
                            <td class="text-end">${{ number_format($total_debit_price) }}{{-- number_format($received_order->price) --}}</td>
                            <td></td>
                            <td class="text-end">${{ number_format($total_credit_price) }}{{-- number_format($received_order->price) --}}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">確認</button>
            <a href="{{ Route('cms.collection_received.receipt', ['id' => $received_order->source_id]) }}" 
                class="btn btn-outline-primary px-4" role="button">返回上一頁
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
