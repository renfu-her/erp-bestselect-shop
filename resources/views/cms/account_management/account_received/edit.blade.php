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
        $ACCOUNT_RECEIVED = \App\Enums\Received\ReceivedMethod::AccountsReceivable;
        $FOREIGN_CURRENCY = \App\Enums\Received\ReceivedMethod::ForeignCurrency;
        $REMIT = \App\Enums\Received\ReceivedMethod::Remittance;
    @endphp

    <form method="post" action="{{ $formAction }}">
        <input type="hidden" name="id[ord_orders]" value="{{ $ord_orders_id }}">
    @csrf
        <div class="row justify-content-end mb-4">
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
                <x-b-form-group title="金額（台幣）" required="true" class="col-12 col-sm-6 mb-2">
                    <input class="form-control @error('tw_price') is-invalid @enderror"
                           name="tw_price"
                           required
                           type="number"
                           step="0.01"
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
                            <option value="" selected disabled>請選擇</option>
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
                                >
                                    {{--                                {{ count($all_payable_type_data['payableCheque']) > 0 &&--}}
                                    {{--$all_payable_type_data['payableCheque']['grade_id_fk'] == $chequeData['grade_id_fk']--}}
                                    {{--? 'selected' : '' }}>--}}
                                    {{ $data['code'] . ' ' . $data['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
                <x-b-form-group name="{{ $CHEQUE }}[check_num]"
                                title="票號"
                                required="true"
                                class="col-12 col-sm-4 mb-3 {{ $CHEQUE }}"
                                id="check_num">
                    <input class="form-control
                                @error($CHEQUE . '[check_num]') is-invalid @enderror"
                           name="{{ $CHEQUE }}[check_num]"
                           required
                           type="text"
                           value="{{ old( $CHEQUE . '[check_num]', $all_payable_type_data['payableCheque']['check_num'] ?? '') }}"/>
                </x-b-form-group>
                <x-b-form-group name="{{ $CHEQUE }}[maturity_date]"
                                title="到期日"
                                required="true"
                                class="col-12 col-sm-4 mb-3 {{ $CHEQUE }}"
                                id="{{ $CHEQUE }}[maturity_date]">
                    <input class="form-control @error($CHEQUE . '[maturity_date]') is-invalid @enderror"
                           name="{{ $CHEQUE }}[maturity_date]"
                           required
                           type="date"
                           value="{{ old($CHEQUE . '[maturity_date]', $all_payable_type_data['payableCheque']['maturity_date'] ?? '') }}"/>
                </x-b-form-group>

                <x-b-form-group name="{{ $REMIT }}[remit_date]" title="匯款日期" required="true"
                                class="col-12 col-sm-4 mb-3 remit">
                    <input class="form-control @error($REMIT . '[remit_date]') is-invalid @enderror"
                           name="{{ $REMIT }}[remit_date]"
                           type="date"
                           required
                           value="{{ old($REMIT . '[remit_date]',  $all_payable_type_data['payableRemit']['remit_date'] ?? '') }}"/>
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
                           value="{{ old( $REMIT . '[bank_slip_name]', $all_payable_type_data['payableCheque']['bank_slip_name'] ?? '') }}"/>
                </x-b-form-group>

                <x-b-form-group name="{{ $FOREIGN_CURRENCY }}[rate]" title="匯率" required="true" class="col-12 col-sm-4 mb-3 {{ $FOREIGN_CURRENCY }}">
                    <input class="form-control @error($FOREIGN_CURRENCY . '[rate]') is-invalid @enderror"
                           name="{{ $FOREIGN_CURRENCY }}[rate]"
                           required
                           id="rate"
                           type="number"
                           step="0.01"
                           value="{{ old('foreign_currency[rate]', $all_payable_type_data['payableForeignCurrency']['rate'] ?? '') }}"/>
                </x-b-form-group>
                <x-b-form-group name="{{ $FOREIGN_CURRENCY }}[foreign_price]" title="金額（外幣）" required="true"
                                class="col-12 col-sm-4 mb-3 {{ $FOREIGN_CURRENCY }}"
                                id="{{ $FOREIGN_CURRENCY }}">
                    <input class="form-control @error($FOREIGN_CURRENCY . '[foreign_price]') is-invalid @enderror"
                           name="{{ $FOREIGN_CURRENCY }}[foreign_price]"
                           required
                           type="number"
                           step="0.01"
                           value="{{ old($FOREIGN_CURRENCY . '[foreign_price]', $all_payable_type_data['payableForeignCurrency'][$FOREIGN_CURRENCY] ?? '') }}"/>
                </x-b-form-group>
            </div>

            <div class="card shadow p-4 mb-4">
                <h6>收款設定</h6>
                <x-b-form-group name="note" title="備註" required="false">
                    <input class="form-control @error('note') is-invalid @enderror"
                           name="note"
                           type="text"
                           value="{{ old('note', $data->note ?? '') }}"/>
                </x-b-form-group>
            </div>

            <div>
                <button type="submit" class="btn btn-primary px-4">確認</button>
                <a onclick="history.back()"
                   class="btn btn-outline-primary px-4"
                   role="button">取消</a>
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
            const REMIT = "{{ \App\Enums\Received\ReceivedMethod::Remittance }}";
            const FOREIGN_CURRENCY = "{{ \App\Enums\Received\ReceivedMethod::ForeignCurrency }}";
            const ACCOUNTS_RECEIVABLE = "{{ \App\Enums\Received\ReceivedMethod::AccountsReceivable }}";
            const OTHER = "{{ \App\Enums\Received\ReceivedMethod::Other }}";
            const REFUND = "{{ \App\Enums\Received\ReceivedMethod::Refund }}";


            //用來控制顯示「各收款方式」的element
            const cashEle = $('.' + CASH);
            const chequeEle = $('.' + CHEQUE);
            const creditCardEle = $('.' + CREDIT_CARD);
            const remitEle = $('.' + REMIT);
            const foreignCurrencyEle = $('.' + FOREIGN_CURRENCY);
            const accountReceivedEle = $('.' + ACCOUNTS_RECEIVABLE);
            const otherEle = $('.' + OTHER);
            const refundEle = $('.' + REFUND);

            //元素：用來控制「各收款方式」是否傳送POST valueD
            const cashNameAttr = $('[name^=' + CASH + ']');
            const chequeNameAttr = $('[name^=' + CHEQUE + ']');
            const creditCardNameAttr = $('[name^=' + CREDIT_CARD + ']');
            const remitNameAttr = $('[name^=' + REMIT + ']');
            const foreignCurrencyNameAttr = $('[name^=' + FOREIGN_CURRENCY + ']');
            const accountReceivedNameAttr = $('[name^=' + ACCOUNTS_RECEIVABLE + ']');
            const otherNameAttr = $('[name^=' + OTHER + ']');
            const refundNameAttr = $('[name^=' + REFUND + ']');

            const currencyRateEle = $('#rate');
            const currencyEle = $('[name^="foreign_currency[currency]"]');
            const foreignPriceEle = $('[name^="foreign_currency[foreign_price]"]');
            const twPriceEle = $('[name=tw_price]');

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
                    foreignCurrencyEle.hide();
                    accountReceivedEle.hide();
                    otherEle.hide();
                    refundEle.hide();

                    // cashNameAttr.prop('disabled', true);
                    chequeNameAttr.prop('disabled', true);
                    creditCardNameAttr.prop('disabled', true);
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
                    remitEle.hide();
                    foreignCurrencyEle.hide();
                    accountReceivedEle.hide();
                    otherEle.hide();
                    refundEle.hide();

                    cashNameAttr.prop('disabled', true);
                    chequeNameAttr.prop('disabled', true);
                    creditCardNameAttr.prop('disabled', true);
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
                    remitEle.hide();
                    foreignCurrencyEle.hide();
                    accountReceivedEle.hide();
                    otherEle.hide();
                    refundEle.hide();

                    // 點擊「收款方式」任一選項後，先disabled所有name屬性的元素付款方式
                    cashNameAttr.prop('disabled', true);
                    chequeNameAttr.prop('disabled', true);
                    creditCardNameAttr.prop('disabled', true);
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

            });

        </script>

    @endpush
@endonce
