@extends('layouts.main')

@section('sub-content')
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
    <div class="pt-2 mb-3">
        <a href="{{ $previou_url }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>

    <form method="POST" action="{{ $form_action }}">
        @csrf
        <div class="card mb-4">
            <h2 class="mx-3 my-3">收款單入款審核</h2>
            <div class="card-body">
                <div class="col-12 mb-3">
                    <label class="form-label">收款單號：</label>
                    <span>{{$received_order->sn}}</span>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">承辦者：</label>
                    <span>{{ $undertaker ? $undertaker->name : '' }}</span>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">訂單編號：</label>
                    <span></span>
                </div>

                <div class="col-12 mb-3">
                    <div class="form-group">
                        <label class="form-label">審核日期：<span class="text-danger">*</span></label>
                        <input type="date" class="form-control col-4 @error('receipt_date') is-invalid @enderror" name="receipt_date" value="{{ old('receipt_date', $received_order->receipt_date ?? date('Y-m-d', strtotime( date('Y-m-d'))) ) }}" aria-label="審核日期">
                    </div>
                    <div class="invalid-feedback">
                        @error('receipt_date')
                        {{ $message }}
                        @enderror
                    </div>
                </div>

                <div class="col-12 mb-3">
                    <div class="form-group">
                        <label class="form-label">發票號碼：</label>
                        <input type="text" class="form-control col-4 @error('invoice_number') is-invalid @enderror" name="invoice_number" value="{{ old('invoice_number', $received_order->invoice_number) }}" aria-label="發票號碼">
                    </div>
                    <div class="invalid-feedback">
                        @error('invoice_number')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>

            <div class="table-responsive tableoverbox">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th scope="col" style="width:10%"></th>
                            <th scope="col" style="width:35%">借</th>
                            <th scope="col" style="width:10%">借方金額</th>
                            <th scope="col" style="width:35%">貸</th>
                            <th scope="col" style="width:10%">貸方金額</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td scope="row"></td>
                            <td>
                                @foreach($debit as $d_key => $d_value)
                                    @if($d_value->received_info)
                                        @if($d_value->received_info->received_method == 'credit_card')
                                            @php
                                                $received_id = $d_value->received_info->received_id;
                                            @endphp
                                            <div class="col-12 mb-3">
                                                {{ $d_key + 1 . '.' . $d_value->method_name }}
                                                <input type="hidden" name="credit_card[{{ $received_id }}][received_id]" value="{{ $d_value->received_info->received_id }}">
                                                <input type="hidden" name="received_method[]" value="credit_card">
                                                <input type="hidden" name="credit_card[{{ $received_id }}][received_method_id]" value="{{ $d_value->received_info->received_method_id }}">
                                            </div>
                                            <div class="col-12 mb-3 form-group">
                                                <label class="form-label">信用卡號：</label>
                                                <input type="text" class="form-control" name="credit_card[{{ $received_id }}][cardnumber]" value="{{ $d_value->received_info->credit_card_number }}" data-placeholder="信用卡號">
                                            </div>
                                            <div class="col-12 mb-3 form-group">
                                                <label class="form-label">刷卡金額：</label>
                                                <span>{{ number_format($d_value->received_info->credit_card_price) }}</span>
                                            </div>
                                            <div class="col-12 mb-3 form-group">
                                                <label class="form-label">刷卡日期：</label>
                                                <input type="date" class="form-control" name="credit_card[{{ $received_id }}][checkout_date]" value="{{ date('Y-m-d', strtotime($d_value->received_info->credit_card_checkout_date ?? date('Y-m-d', strtotime( date('Y-m-d'))) )) }}" data-placeholder="刷卡日期">
                                            </div>
                                            <div class="col-12 mb-3 form-group">
                                                <label class="form-label">信用卡別：</label>
                                                <select class="form-select -select2 -single" name="credit_card[{{ $received_id }}][card_type_code]" data-placeholder="請選擇信用卡別">
                                                    <option value="">請選擇</option>
                                                    @foreach($card_type as $key => $value)
                                                        <option value="{{ $key }}"{{ $key == $d_value->received_info->credit_card_type_code ? 'selected' : ''}}>{{ $value }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 mb-3 form-group">
                                                <label class="form-label">持卡人：</label>
                                                <input type="text" class="form-control" name="credit_card[{{ $received_id }}][card_owner_name]" value="{{ $d_value->received_info->credit_card_owner_name }}" data-placeholder="持卡人">
                                            </div>
                                            <div class="col-12 mb-3 form-group">
                                                <label class="form-label">授權碼：</label>
                                                <input type="text" class="form-control" name="credit_card[{{ $received_id }}][authcode]" value="{{ $d_value->received_info->credit_card_authcode }}" data-placeholder="授權碼">
                                            </div>
                                            <div class="col-12 mb-3 form-group">
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
                                            <div class="col-12 mb-3 form-group">
                                                <label class="form-label">結帳地區：</label>
                                                <select class="form-select -select2 -single" name="credit_card[{{ $received_id }}][checkout_area_code]" data-placeholder="請選擇結帳地區">
                                                    <option value="">請選擇</option>
                                                    @foreach($checkout_area as $key => $value)
                                                        <option value="{{ $key }}"{{ $key == $d_value->received_info->credit_card_area_code ? 'selected' : ''}}>{{ $value }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @elseif($d_value->received_info->received_method == 'remit')
                                            <div class="col-12 mb-3">{{ $d_key + 1 . '.' . $d_value->method_name . ' ' . $d_value->account_code . ' - ' . $d_value->account_name . ' - ' . $d_value->summary . '（' . $d_value->received_info->remit_memo . '）' }}</div>
                                        @elseif($d_value->received_info->received_method == 'cheque')
                                            @php
                                                $received_id = $d_value->received_info->received_id;
                                            @endphp
                                            <div class="col-12 mb-3">
                                                {{ $d_key + 1 . '.' . $d_value->method_name . ' ' . $d_value->received_info->cheque_ticket_number . '(' . date('Y-m-d', strtotime($d_value->received_info->cheque_due_date)) . ')'}}
                                                <input type="hidden" name="cheque[{{ $received_id }}][received_id]" value="{{ $d_value->received_info->received_id }}">
                                                <input type="hidden" name="received_method[]" value="cheque">
                                                <input type="hidden" name="cheque[{{ $received_id }}][received_method_id]" value="{{ $d_value->received_info->received_method_id }}">
                                            </div>
                                            <div class="col-12 mb-3 form-group">
                                                <label class="form-label">票號：<span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="cheque[{{ $received_id }}][ticket_number]" value="{{ $d_value->received_info->cheque_ticket_number }}" data-placeholder="票號" required>
                                            </div>
                                            <div class="col-12 mb-3 form-group">
                                                <label class="form-label">金額：</label>
                                                <span>{{ number_format($d_value->received_info->tw_price) }}</span>
                                            </div>
                                            <div class="col-12 mb-3 form-group">
                                                <label class="form-label">到期日：<span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" name="cheque[{{ $received_id }}][due_date]" value="{{ date('Y-m-d', strtotime($d_value->received_info->cheque_due_date ?? date('Y-m-d', strtotime( date('Y-m-d'))) )) }}" data-placeholder="到期日" required>
                                            </div>
                                            <div class="col-12 mb-3 form-group">
                                                <label class="form-label">兌現日：</label>
                                                <input type="date" class="form-control" name="cheque[{{ $received_id }}][cashing_date]" value="{{ date('Y-m-d', strtotime($d_value->received_info->cheque_cashing_date ?? date('Y-m-d', strtotime( date('Y-m-d'))) ))  }}" data-placeholder="兌現日">
                                            </div>
                                            <div class="col-12 mb-3 form-group">
                                                <label class="form-label">抽票日：</label>
                                                <input type="date" class="form-control" name="cheque[{{ $received_id }}][draw_date]" value="{{ date('Y-m-d', strtotime($d_value->received_info->cheque_draw_date ?? date('Y-m-d', strtotime( date('Y-m-d'))) ))  }}" data-placeholder="抽票日">
                                            </div>
                                            <div class="col-12 mb-3 form-group">
                                                <label class="form-label">發票銀行：</label>
                                                <input type="text" class="form-control" name="cheque[{{ $received_id }}][banks]" value="{{ $d_value->received_info->cheque_banks }}" data-placeholder="發票銀行">
                                            </div>
                                            <div class="col-12 mb-3 form-group">
                                                <label class="form-label">付款帳號：</label>
                                                <input type="text" class="form-control" name="cheque[{{ $received_id }}][accounts]" value="{{ $d_value->received_info->cheque_accounts }}" data-placeholder="付款帳號">
                                            </div>
                                            <div class="col-12 mb-3 form-group">
                                                <label class="form-label">發票人：</label>
                                                <input type="text" class="form-control" name="cheque[{{ $received_id }}][drawer]" value="{{ $d_value->received_info->cheque_drawer }}" data-placeholder="發票人">
                                            </div>
                                            <div class="col-12 mb-3 form-group">
                                                <label class="form-label">會計科目：<span class="text-danger">*</span></label>
                                                <select class="form-select -select2 -single" name="cheque[{{ $received_id }}][all_grades_id]" data-placeholder="請選擇會計科目" required>
                                                    <option value="" selected disabled>請選擇</option>
                                                    @foreach($cheque_grade as $value)
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
                                            <div class="col-12 mb-3 form-group">
                                                <label class="form-label">票據狀態：<span class="text-danger">*</span></label>
                                                <select class="form-select -select2 -single" name="cheque[{{ $received_id }}][status_code]" data-placeholder="請選擇票據狀態" required>
                                                    <option value="">請選擇</option>
                                                    @foreach($cheque_status as $key => $value)
                                                        <option value="{{ $key }}"{{ $key == $d_value->received_info->cheque_status_code ? 'selected' : ''}}>{{ $value }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 mb-3 form-group">
                                                <label class="form-label">存入地區：</label>
                                                <select class="form-select -select2 -single" name="cheque[{{ $received_id }}][deposited_area_code]" data-placeholder="請選擇存入地區">
                                                    <option value="">請選擇</option>
                                                    @foreach($checkout_area as $key => $value)
                                                        <option value="{{ $key }}"{{ $key == $d_value->received_info->cheque_deposited_area_code ? 'selected' : ''}}>{{ $value }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @else
                                            <div class="col-12 mb-3">{{ $d_key + 1 . '.' . $d_value->name }}</div>
                                        @endif
                                    @endif
                                @endforeach
                            </td>
                            <td>
                                @php
                                $total_debit_price = 0;
                                foreach($debit as $value){
                                    echo number_format($value->price) . "<br>";
                                    $total_debit_price += $value->price;
                                }
                                @endphp
                            </td>

                            <td>
                                @foreach($credit as $value)
                                {{ $value->name }}
                                <br>
                                @endforeach
                            </td>
                            <td>
                                @php
                                $total_credit_price = 0;
                                foreach($credit as $value){
                                    echo number_format($value->price) . "<br>";
                                    $total_credit_price += $value->price;
                                }
                                @endphp
                            </td>
                        </tr>

                        <tr class="table-light">
                            <td>合計：</td>
                            <td></td>
                            <td>{{ number_format($total_debit_price) }}{{-- number_format($received_order->price) --}}</td>
                            <td></td>
                            <td>{{ number_format($total_credit_price) }}{{-- number_format($received_order->price) --}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="px-0">
            <button type="submit" class="btn btn-primary px-4">確認</button>
            {{-- <a onclick="history.back()" class="btn btn-outline-primary px-4" role="button">取消</a> --}}
        </div>
    </form>
@endsection

@once
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
