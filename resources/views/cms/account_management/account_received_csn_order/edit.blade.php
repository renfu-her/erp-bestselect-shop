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
    @php
        $CHEQUE = \App\Enums\Received\ReceivedMethod::Cheque;
        $CREDIT_CARD = \App\Enums\Received\ReceivedMethod::CreditCard;
        $ACCOUNT_RECEIVED = \App\Enums\Received\ReceivedMethod::AccountsReceivable;
        $FOREIGN_CURRENCY = \App\Enums\Received\ReceivedMethod::ForeignCurrency;
        $REMIT = \App\Enums\Received\ReceivedMethod::Remittance;
    @endphp

    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.consignment-order.edit', ['id' => $ord_orders_id]) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>
    <form method="POST" action="{{ $formAction }}">
        @csrf
        <input type="hidden" name="id" value="{{ $ord_orders_id }}">
        <div class="row justify-content-end mb-4">
            <h2 class="mb-4">收款管理</h2>
            <div class="card shadow p-4 mb-4">
                {{-- <h6>收款紀錄</h6> --}}

                <div class="card-body">
                    <div class="col">
                        <dl class="row mb-0">
                            <dt>收款單明細：{{ $order_purchaser->name ?? '' }}</dt>
                        </dl>
                    </div>
                </div>

                <div class="table-responsive tableOverBox">
                    <table class="table table-hover table-bordered tableList mb-0">
                        <thead>
                            <tr>
                                <th scope="col">請款單號</th>
                                <th scope="col">說明</th>
                                <th scope="col">單價</th>
                                <th scope="col">數量</th>
                                <th scope="col">匯率</th>
                                <th scope="col">幣別</th>
                                <th scope="col">應收款項</th>
                                <th scope="col">已收款項</th>
                            </tr>
                        </thead>

                        <tbody class="product_list">
                            @foreach($order_list_data as $value)
                            <tr>
                                <td>{{ $received_order_data->first()->sn }}</td>
                                <td>{{ $value->product_title }}{{'（' . $value->product_price . ' * ' . $value->product_qty . '）'}}</td>
                                <td class="text-end">{{ number_format($value->product_price, 2) }}</td>
                                <td class="text-end">{{$value->product_qty}}</td>
                                <td class="text-end">1</td>
                                <td>NTD</td>
                                <td class="text-end">{{ number_format($value->product_origin_price) }}</td>
                                <td class="text-end"></td>
                            </tr>
                            @endforeach
                            @if($order_data->dlv_fee > 0)
                            <tr>
                                <td>{{ $received_order_data->first()->sn }}</td>
                                <td>物流費用</td>
                                <td class="text-end">{{ number_format($order_data->dlv_fee, 2) }}</td>
                                <td class="text-end">1</td>
                                <td class="text-end">1</td>
                                <td>NTD</td>
                                <td class="text-end">{{ number_format($order_data->dlv_fee) }}</td>
                                <td class="text-end"></td>
                            </tr>
                            @endif
                            @if($order_data->discount_value > 0)
                                @foreach($order_discount ?? [] as $d_value)
                                <tr>
                                    <td>{{ $received_order_data->first()->sn }}</td>
                                    <td>{{ $d_value->account_code }} {{ $d_value->account_name }} - {{ $d_value->title }}</td>
                                    <td class="text-end">-{{ number_format($d_value->discount_value, 2) }}</td>
                                    <td class="text-end">1</td>
                                    <td class="text-end">1</td>
                                    <td>NTD</td>
                                    <td class="text-end">-{{ number_format($d_value->discount_value) }}</td>
                                    <td class="text-end"></td>
                                </tr>
                                @endforeach
                            @endif
                            @foreach($received_data as $value)
                            <tr>
                                <td>{{ $received_order_data->first()->sn }}</td>
                                <td>{{ $value->received_method_name }} {{ $value->note }}{{ '（' . $value->account->code . ' - ' . $value->account->name . '）'}}</td>
                                <td class="text-end">{{ number_format($value->tw_price, 2) }}</td>
                                <td class="text-end">1</td>
                                <td class="text-end">{{ $value->currency_rate }}</td>
                                <td>{{ $value->currency_name }}</td>
                                <td class="text-end"></td>
                                <td class="text-end">{{ number_format($value->tw_price) }}</td>
                            </tr>
                            @endforeach
                        </tbody>

                        <tfoot>
                            <tr>
                                <th scope="row" colspan="10" class="text-end">應收總計金額：{{ number_format($tw_price) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="card shadow p-4 mb-4">
                <fieldset class="col-12 mb-4 ">
                    <h6>收款方式
                        <span class="text-danger">*</span>
                    </h6>
                    @php
                        $isFirst = true;
                    @endphp
                    @foreach($receivedMethods as $name => $receivedMethod)
                        <div class="form-check form-check-inline">
                            <label class="form-check-label transactType" data-type="{{ $name }}">
                                <input class="form-check-input"
                                    name="acc_transact_type_fk"
                                    type="radio"
                                    @if($isFirst)
                                        checked
                                    @endif
                                    @php
                                        $isFirst = false;
                                    @endphp
                                    value="{{ $name }}">
                                {{ $receivedMethod }}
                            </label>
                        </div>
                    @endforeach
                </fieldset>
                <x-b-form-group title="金額（台幣）" required="true" class="col-12 col-sm-4 mb-3">
                    <input class="form-control @error('tw_price') is-invalid @enderror"
                           name="tw_price"
                           required
                           type="text"
                           value="{{ old('tw_price', $tw_price ?? '') }}"/>
                </x-b-form-group>

                @foreach($defaultArray as $methodName => $defaultData)
                    <div class="col-12 col-sm-4 mb-3 {{ $methodName }}">
                        <label for="" class="form-label {{ $methodName }}">
                            會計科目
                            <span class="text-danger">*</span>
                        </label>
                        <select name="{{$methodName}}[grade]"
                                class="form-select -select2 -single {{$methodName}} @error($methodName) is-invalid @enderror"
                                required data-placeholder="請選擇會計科目">
                            @php
                                $check_first = true;
                            @endphp
                            @foreach($defaultData as $gradeId => $data)
                                <option value="{{ $gradeId }}"
                                    @if($data['grade_num'] === 1)
                                        class="grade_1"
                                    @elseif($data['grade_num'] === 2)
                                        class="grade_2"
                                    @elseif($data['grade_num'] === 3)
                                        class="grade_3"
                                    @elseif($data['grade_num'] === 4)
                                        class="grade_4"
                                    @endif
                                    {{ $check_first ? 'selected' : '' }}
                                >{{ $data['code'] . ' ' . $data['name'] }}</option>
                                @php
                                    $check_first = false;
                                @endphp
                            @endforeach
                        </select>
                    </div>
                @endforeach
                <x-b-form-group name="{{ $CHEQUE }}[ticket_number]"
                                title="票號"
                                required="true"
                                class="col-12 col-sm-4 mb-3 {{ $CHEQUE }}"
                                id="ticket_number">
                    <input class="form-control
                                @error($CHEQUE . '[ticket_number]') is-invalid @enderror"
                           name="{{ $CHEQUE }}[ticket_number]"
                           required
                           type="text"
                           value="{{ old( $CHEQUE . '[ticket_number]', '') }}"/>
                </x-b-form-group>
                <x-b-form-group name="{{ $CHEQUE }}[due_date]"
                                title="到期日"
                                required="true"
                                class="col-12 col-sm-4 mb-3 {{ $CHEQUE }}"
                                id="{{ $CHEQUE }}[due_date]">
                    <input class="form-control @error($CHEQUE . '[due_date]') is-invalid @enderror"
                           name="{{ $CHEQUE }}[due_date]"
                           required
                           type="date"
                           value="{{ old($CHEQUE . '[due_date]', date('Y-m-d', strtotime( date('Y-m-d')))) }}"/>
                </x-b-form-group>

                {{-- credit card --}}
                <x-b-form-group name="{{ $CREDIT_CARD }}[card_owner_name]" title="持卡人" class="col-12 col-sm-4 mb-3 {{ $CREDIT_CARD }}" id="{{ $CREDIT_CARD }}[card_owner_name]">
                    <input type="text" class="form-control @error($CREDIT_CARD . '[card_owner_name]') is-invalid @enderror" name="{{ $CREDIT_CARD }}[card_owner_name]" value="{{ old($CREDIT_CARD . '[card_owner_name]') }}">
                </x-b-form-group>
                <div class="col-12 col-sm-4 mb-3 {{ $CREDIT_CARD }}">
                    <label for="" class="form-label {{ $CREDIT_CARD }}">信用卡別</label>
                    <select class="form-select -select2 -single" name="{{ $CREDIT_CARD }}[card_type_code]" data-placeholder="請選擇信用卡別">
                        <option value="">請選擇</option>
                        @foreach($card_type as $key => $value)
                            <option value="{{ $key }}"{{ $key == old($CREDIT_CARD . '[card_type_code]') ? 'selected' : ''}}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <x-b-form-group name="{{ $CREDIT_CARD }}[cardnumber]" title="卡號" class="col-12 col-sm-4 mb-3 {{ $CREDIT_CARD }}" id="{{ $CREDIT_CARD }}[cardnumber]">
                    <input type="text" class="form-control @error($CREDIT_CARD . '[cardnumber]') is-invalid @enderror" name="{{ $CREDIT_CARD }}[cardnumber]" value="{{ old($CREDIT_CARD . '[cardnumber]') }}">
                </x-b-form-group>
                <x-b-form-group name="{{ $CREDIT_CARD }}[checkout_date]" title="刷卡日期" class="col-12 col-sm-4 mb-3 {{ $CREDIT_CARD }}" id="{{ $CREDIT_CARD }}[checkout_date]">
                    <input type="date" class="form-control @error($CREDIT_CARD . '[checkout_date]') is-invalid @enderror" name="{{ $CREDIT_CARD }}[checkout_date]" value="{{ old($CREDIT_CARD . '[checkout_date]', date('Y-m-d', strtotime( date('Y-m-d'))) ) }}">
                </x-b-form-group>
                <x-b-form-group name="{{ $CREDIT_CARD }}[authcode]" title="授權碼" class="col-12 col-sm-4 mb-3 {{ $CREDIT_CARD }}" id="{{ $CREDIT_CARD }}[authcode]">
                    <input type="text" class="form-control @error($CREDIT_CARD . '[authcode]') is-invalid @enderror" name="{{ $CREDIT_CARD }}[authcode]" value="{{ old($CREDIT_CARD . '[authcode]') }}">
                </x-b-form-group>
                {{--
                <div class="col-12 col-sm-4 mb-3 {{ $CREDIT_CARD }}">
                    <label for="" class="form-label {{ $CREDIT_CARD }}">結帳地區</label>
                    <select class="form-select -select2 -single" name="{{ $CREDIT_CARD }}[credit_card_area_code]" data-placeholder="請選擇結帳地區">
                        <option value="">請選擇</option>
                        @foreach($checkout_area as $key => $value)
                            <option value="{{ $key }}"{{ $key == old($CREDIT_CARD . '[credit_card_area_code]') ? 'selected' : ''}}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-4 mb-3 {{ $CREDIT_CARD }}">
                    <label for="" class="form-label {{ $CREDIT_CARD }}">信用卡分期數</label>
                    <select class="form-select -select2 -single" name="{{ $CREDIT_CARD }}[installment]" data-placeholder="請選擇信用卡分期數">
                        <option value="none">不分期</option>
                    </select>
                </div>
                --}}


                <x-b-form-group name="{{ $REMIT }}[remittance]" title="匯款日期" required="true"
                                class="col-12 col-sm-4 mb-3 remit">
                    <input class="form-control @error($REMIT . '[remittance]') is-invalid @enderror"
                           name="{{ $REMIT }}[remittance]"
                           type="date"
                           required
                           value="{{ old($REMIT . '[remittance]', date('Y-m-d', strtotime( date('Y-m-d')))) }}"/>
                </x-b-form-group>
                <x-b-form-group name="{{ $REMIT }}[bank_slip_name]"
                                title="水單末5碼或匯款人姓名"
                                required="true"
                                class="col-12 col-sm-4 mb-3 {{ $REMIT }}"
                                id="bank_slip_name">
                    <input class="form-control
                                @error($REMIT . '[bank_slip_name]') is-invalid @enderror"
                           name="{{ $REMIT }}[bank_slip_name]"
                           required
                           type="text"
                           value="{{ old( $REMIT . '[bank_slip_name]', '') }}"/>
                </x-b-form-group>

                <x-b-form-group name="{{ $FOREIGN_CURRENCY }}[rate]" title="匯率" required="true" class="col-12 col-sm-4 mb-3 {{ $FOREIGN_CURRENCY }}">
                    <input class="form-control @error($FOREIGN_CURRENCY . '[rate]') is-invalid @enderror"
                           name="{{ $FOREIGN_CURRENCY }}[rate]"
                           required
                           id="rate"
                           type="number"
                           step="0.01"
                           value="{{ old($FOREIGN_CURRENCY . '[rate]', '') }}"/>
                </x-b-form-group>
                <x-b-form-group name="{{ $FOREIGN_CURRENCY }}[foreign_price]" title="金額（外幣）" required="true"
                                class="col-12 col-sm-4 mb-3 {{ $FOREIGN_CURRENCY }}"
                                id="{{ $FOREIGN_CURRENCY }}">
                    <input class="form-control @error($FOREIGN_CURRENCY . '[foreign_price]') is-invalid @enderror"
                           name="{{ $FOREIGN_CURRENCY }}[foreign_price]"
                           required
                           type="number"
                           step="0.01"
                           value="{{ old($FOREIGN_CURRENCY . '[foreign_price]', '') }}"/>
                </x-b-form-group>
            </div>

            <div class="card shadow p-4 mb-4">
                <h6>收款設定</h6>
            <div class="row">
                <x-b-form-group name="summary" title="摘要" required="false" class="col-12 col-sm-6 mb-3">
                    <input class="form-control @error('summary') is-invalid @enderror" name="summary" type="text" value="{{ old('summary', '') }}">
                </x-b-form-group>
                <x-b-form-group name="note" title="備註" required="false" class="col-12 col-sm-6 mb-3">
                    <input class="form-control @error('note') is-invalid @enderror"
                           name="note"
                           type="text"
                           value="{{ old('note', '') }}"/>
                </x-b-form-group>
            </div>

            <div class="px-0">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
                <a onclick="history.back()" class="btn btn-outline-primary px-4" role="button">取消</a>
            </div>
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

            //收款方式ID數值
            const CASH = "{{ \App\Enums\Received\ReceivedMethod::Cash }}";
            const CHEQUE = "{{ \App\Enums\Received\ReceivedMethod::Cheque }}";
            const CREDIT_CARD = "{{ \App\Enums\Received\ReceivedMethod::CreditCard }}";
            // const CREDIT_CARD_3 = "{{-- \App\Enums\Received\ReceivedMethod::CreditCard3 --}}";
            const REMIT = "{{ \App\Enums\Received\ReceivedMethod::Remittance }}";
            const FOREIGN_CURRENCY = "{{ \App\Enums\Received\ReceivedMethod::ForeignCurrency }}";
            const ACCOUNTS_RECEIVABLE = "{{ \App\Enums\Received\ReceivedMethod::AccountsReceivable }}";
            const OTHER = "{{ \App\Enums\Received\ReceivedMethod::Other }}";
            const REFUND = "{{ \App\Enums\Received\ReceivedMethod::Refund }}";


            //用來控制顯示「各收款方式」的element
            const cashEle = $('.' + CASH);
            const chequeEle = $('.' + CHEQUE);
            const creditCardEle = $('.' + CREDIT_CARD);
            // const creditCard3Ele = $('.' + CREDIT_CARD_3);
            const remitEle = $('.' + REMIT);
            const foreignCurrencyEle = $('.' + FOREIGN_CURRENCY);
            const accountReceivedEle = $('.' + ACCOUNTS_RECEIVABLE);
            const otherEle = $('.' + OTHER);
            const refundEle = $('.' + REFUND);

            //元素：用來控制「各收款方式」是否傳送POST valueD
            const cashNameAttr = $('[name^=' + CASH + ']');
            const chequeNameAttr = $('[name^=' + CHEQUE + ']');
            const creditCardNameAttr = $('[name^=' + CREDIT_CARD + ']');
            // const creditCard3NameAttr = $('[name^=' + CREDIT_CARD_3 + ']');
            const remitNameAttr = $('[name^=' + REMIT + ']');
            const foreignCurrencyNameAttr = $('[name^=' + FOREIGN_CURRENCY + ']');
            const accountReceivedNameAttr = $('[name^=' + ACCOUNTS_RECEIVABLE + ']');
            const otherNameAttr = $('[name^=' + OTHER + ']');
            const refundNameAttr = $('[name^=' + REFUND + ']');

            const currencyJson = @json($currencyDefaultArray);
            const currencyRateEle = $('#rate');
            const foreignPriceEle = $('[name^="' + FOREIGN_CURRENCY + '[foreign_price]"]');
            const twPriceEle = $('[name=tw_price]');

            //選擇外幣後，自動帶入匯率、外幣金額、會計科目
            foreignCurrencyEle.on('change', function () {
                $selectedCurrency = $('.' + FOREIGN_CURRENCY + ' select');
                currencyRateEle.val(currencyJson[$selectedCurrency.select2().val()][0]['rate']);
                let foreignPrice = (twPriceEle.val() / currencyRateEle.val()).toFixed(2);
                foreignPriceEle.val(foreignPrice);
            });

            const transactTypeEle = $('.transactType');
            const transactTypeSelectedRadioEle = $('.transactType input:checked');

            $(document).ready(function () {
                let selectedType = transactTypeSelectedRadioEle.val();

                //初次新create建立，只顯示現金畫面
                if (selectedType === CASH) {
                    // cashEle.hide();
                    chequeEle.hide();
                    remitEle.hide();
                    creditCardEle.hide();
                    // creditCard3Ele.hide();
                    foreignCurrencyEle.hide();
                    accountReceivedEle.hide();
                    otherEle.hide();
                    refundEle.hide();

                    // cashNameAttr.prop('disabled', true);
                    chequeNameAttr.prop('disabled', true);
                    creditCardNameAttr.prop('disabled', true);
                    // creditCard3NameAttr.prop('disabled', true);
                    remitNameAttr.prop('disabled', true);
                    foreignCurrencyNameAttr.prop('disabled', true);
                    accountReceivedNameAttr.prop('disabled', true);
                    otherNameAttr.prop('disabled', true);
                    refundNameAttr.prop('disabled', true);

                }
                else {
                    //資料庫已經有記錄，先隱藏所有選項
                    cashEle.hide();
                    chequeEle.hide();
                    creditCardEle.hide();
                    // creditCard3Ele.hide();
                    remitEle.hide();
                    foreignCurrencyEle.hide();
                    accountReceivedEle.hide();
                    otherEle.hide();
                    refundEle.hide();

                    cashNameAttr.prop('disabled', true);
                    chequeNameAttr.prop('disabled', true);
                    creditCardNameAttr.prop('disabled', true);
                    // creditCard3NameAttr.prop('disabled', true);
                    remitNameAttr.prop('disabled', true);
                    foreignCurrencyNameAttr.prop('disabled', true);
                    accountReceivedNameAttr.prop('disabled', true);
                    otherNameAttr.prop('disabled', true);
                    refundNameAttr.prop('disabled', true);
                }

                // 只顯示出資料庫有的「收款方式」
                switch (parseInt(selectedType, 10)) {
                    case CASH:
                        cashEle.show();
                        cashNameAttr.prop('disabled', false);
                        break;
                    case CHEQUE:
                        chequeEle.show();
                        chequeNameAttr.prop('disabled', false);
                        break;
                    case CREDIT_CARD:
                        creditCardEle.show();
                        creditCardNameAttr.prop('disabled', false);
                        break;
                    // case CREDIT_CARD_3:
                    //     creditCard3Ele.show();
                    //     creditCard3NameAttr.prop('disabled', false);
                    //     break;
                    case REMIT:
                        remitEle.show();
                        remitNameAttr.prop('disabled', false);
                        break;
                    case FOREIGN_CURRENCY:
                        foreignCurrencyEle.show();
                        foreignCurrencyNameAttr.prop('disabled', false);
                        break;
                    case ACCOUNTS_RECEIVABLE:
                        accountReceivedEle.show();
                        accountReceivedNameAttr.prop('disabled', false);
                        break;
                    case OTHER:
                        otherEle.show();
                        otherNameAttr.prop('disabled', false);
                        break;
                    case REFUND:
                        refundEle.show();
                        refundNameAttr.prop('disabled', false);
                        break;
                    default:
                        cashEle.show();
                        cashNameAttr.prop('disabled', false);
                }

                transactTypeEle.on('change', function () {
                    dataType = $(this).attr('data-type');

                    // 點擊「收款方式」任一選項後，先隱藏所有「收款方式」的元素
                    cashEle.hide();
                    chequeEle.hide();
                    creditCardEle.hide();
                    // creditCard3Ele.hide();
                    remitEle.hide();
                    foreignCurrencyEle.hide();
                    accountReceivedEle.hide();
                    otherEle.hide();
                    refundEle.hide();

                    // 點擊「收款方式」任一選項後，先disabled所有name屬性的元素付款方式
                    cashNameAttr.prop('disabled', true);
                    chequeNameAttr.prop('disabled', true);
                    creditCardNameAttr.prop('disabled', true);
                    // creditCard3NameAttr.prop('disabled', true);
                    remitNameAttr.prop('disabled', true);
                    foreignCurrencyNameAttr.prop('disabled', true);
                    accountReceivedNameAttr.prop('disabled', true);
                    otherNameAttr.prop('disabled', true);
                    refundNameAttr.prop('disabled', true);

                    //只顯示勾選到的「收款方式」元素、並傳送該name屬性value到後端
                    switch (dataType) {
                        case CASH:
                            cashEle.show();
                            cashNameAttr.prop('disabled', false);
                            break;
                        case CHEQUE:
                            chequeEle.show();
                            chequeNameAttr.prop('disabled', false);
                            break;
                        case CREDIT_CARD:
                            creditCardEle.show();
                            creditCardNameAttr.prop('disabled', false);
                            break;
                        // case CREDIT_CARD_3:
                        //     creditCard3Ele.show();
                        //     creditCard3NameAttr.prop('disabled', false);
                        //     break;
                        case REMIT:
                            remitEle.show();
                            remitNameAttr.prop('disabled', false);
                            break;
                        case FOREIGN_CURRENCY:
                            foreignCurrencyEle.show();
                            foreignCurrencyNameAttr.prop('disabled', false);
                            break;
                        case ACCOUNTS_RECEIVABLE:
                            accountReceivedEle.show();
                            accountReceivedNameAttr.prop('disabled', false);
                            break;
                        case OTHER:
                            otherEle.show();
                            otherNameAttr.prop('disabled', false);
                            break;
                        case REFUND:
                            refundEle.show();
                            refundNameAttr.prop('disabled', false);
                            break;
                        default:
                            cashEle.show();
                    }
                });

                $('form').submit(function(e) {
                    if($('input[name="acc_transact_type_fk"]:checked').val() == 'cheque'){
                        const reg = new RegExp(/^[A-Z]{2}[0-9]{7}$/);
                        if(! reg.test($('input[name="cheque[ticket_number]"]').val())){
                            $('input[name="cheque[ticket_number]"]').addClass('is-invalid');
                            e.preventDefault();
                            return false;
                        }
                    }
                });
            });
        </script>
    @endpush
@endonce
