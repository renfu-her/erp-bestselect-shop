@extends('layouts.main')
@section('sub-content')
    <style>
        .cash,
        .cheque,
        .remit,
        .foreign_currency,
        .payable_account,
        .other {
            display: none;
        }
    </style>
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.ap.index', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回付款作業管理
        </a>
    </div>
    <form method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf
        <div class="row justify-content-end mb-4">
            <h2 class="mb-4">付款管理</h2>
            <div class="card shadow p-4 mb-4">
                <fieldset class="col-12 mb-4 ">
                    <h6>付款方式
                        <span class="text-danger">*</span>
                    </h6>
                    @foreach($transactTypeList as $transactData)
                        <div class="form-check form-check-inline">
                            <label class="form-check-label transactType" data-type="{{ $transactData['key'] }}">
                                <input class="form-check-input"
                                       name="acc_transact_type_fk"
                                       type="radio"
                                       value="{{ $transactData['value'] }}">
                                {{ $transactData['name'] }}
                            </label>
                        </div>
                    @endforeach
                </fieldset>

                <x-b-form-group title="金額（台幣）" required="true" class="col-12 col-sm-4 mb-3">
                    <input class="form-control @error('tw_price') is-invalid @enderror"
                           name="tw_price"
                           required
                           type="number"
                           step="0.01"
                           value="{{ old('tw_price', $tw_price ?? '') }}"/>
                </x-b-form-group>

                <label for="" class="form-label cash">科目
                    <span class="text-danger">*</span>
                </label>
                <select
                    name="cash[grade_id_fk]"
                    class="-select2 -single form-select col-12 col-sm-4 mb-3 cash
                        @error('cash[grade_id_fk]') is-invalid @enderror"
                    required
                    data-placeholder="請選擇">
                    <option disabled selected value> -- select an option --</option>

                    @foreach($cashDefault as $cashData)
                        <option
                            value="{{ $cashData['grade_id_fk'] }}">{{ $cashData['code'] . ' ' . $cashData['name'] }}
                        </option>
                    @endforeach
                </select>

                {{--                Start of 支票--}}
                <label for="" class="form-label cheque">支存銀行
                    <span class="text-danger">*</span>
                </label>
                <select
                    name="cheque[grade_id_fk]"
                    required
                    class="-select2 -single form-select col-12 col-sm-4 mb-3 cheque @error('cheque[grade_id_fk]') is-invalid @enderror"
                    data-placeholder="請選擇">
                    <option disabled selected value> -- select an option --</option>
                    @foreach($chequeDefault as $chequeData)
                        <option
                            value="{{ $chequeData['grade_id_fk'] }}">{{ $chequeData['code'] . ' ' . $chequeData['name'] }}
                        </option>
                    @endforeach
                </select>

                <x-b-form-group name="cheque[check_num]" title="票號" required="true"
                                class="col-12 col-sm-4 mb-3 cheque"
                                id="check_num">
                    <input class="form-control @error('cheque[check_num]') is-invalid @enderror"
                           name="cheque[check_num]"
                           required
                           type="text"
                           value="{{ old('check_num', $data->check_num ?? '') }}"/>
                </x-b-form-group>
                <x-b-form-group name="cheque[maturity_date]"
                                title="到期日"
                                required="true"
                                class="col-12 col-sm-4 mb-3 cheque"
                                id="cheque[maturity_date]">
                    <input class="form-control @error('cheque[maturity_date]') is-invalid @enderror"
                           name="cheque[maturity_date]"
                           required
                           type="date"
                           value="{{ old('maturity_date', $data->maturity_date ?? '') }}"/>
                </x-b-form-group>
                <x-b-form-group name="cheque[cash_cheque_date]" title="兌現日" required="true"
                                class="col-12 col-sm-4 mb-3 cheque"
                                id="cheque[cash_cheque_date]">
                    <input class="form-control @error('cheque[cash_cheque_date]') is-invalid @enderror"
                           name="cheque[cash_cheque_date]"
                           required
                           type="date"
                           value="{{ old('check_num', $data->check_num ?? '') }}"/>
                </x-b-form-group>
                <div class="col-12 col-sm-4 mb-3 cheque">
                    <label class="form-label">狀態
                        <span class="text-danger">*</span>
                    </label>
                    <select name="cheque[cheque_status]" class="form-select" aria-label="Select" required>
                        <option value=""></option>
                        @foreach($chequeStatus as $chequeData)
                            <option value="{{ $chequeData['id'] }}">
                                {{ $chequeData['status'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                {{--                End of 支票--}}

                <label for="" class="form-label remit">匯款銀行
                    <span class="text-danger">*</span>
                </label>
                <select
                    name="remit[grade_id_fk]"
                    class="-select2 -single form-select col-12 col-sm-4 mb-3 remit @error('remit[grade_id_fk]') is-invalid @enderror"
                    required
                    data-placeholder="請選擇">
                    <option disabled selected value> -- select an option --</option>
                    @foreach($remitDefault as $remitData)
                        <option
                            value="{{ $remitData['grade_id_fk'] }}">{{ $remitData['code'] . ' ' . $remitData['name'] }}
                        </option>
                    @endforeach
                </select>
                <x-b-form-group name="remit[remit_date]" title="匯款日期" required="true"
                                class="col-12 col-sm-4 mb-3 remit">
                    <input class="form-control @error('remit[remit_date]') is-invalid @enderror"
                           name="remit[remit_date]"
                           type="date"
                           required
                           value="{{ old('remit[remit_date]', $data->remit_date ?? '') }}"/>
                </x-b-form-group>

                <label for="" class="form-label foreign_currency">外幣
                    <span class="text-danger">*</span>
                </label>
                <select
                    name="foreign_currency[currency]"
                    class="-select2 -single form-select col-12 col-sm-4 mb-3 foreign_currency @error('foreign_currency[currency]') is-invalid @enderror"
                    required
                    data-placeholder="請選擇">
                    <option disabled selected value> -- select an option --</option>
                    @foreach($currencyDefault as $currencyData)
                        <option
                            value="{{ $currencyData['currency_id'] }}">{{ $currencyData['currency'] }}
                        </option>
                    @endforeach
                </select>

                <x-b-form-group name="foreign_currency[rate]" title="匯率" required="true"
                                class="col-12 col-sm-4 mb-3 foreign_currency"
                >
                    <input class="form-control @error('foreign_currency[rate]') is-invalid @enderror"
                           name="foreign_currency[rate]"
                           required
                           id="rate"
                           type="number"
                           step="0.01"
                           value="{{ old('foreign_currency[rate]', $data->rate ?? '') }}"/>
                </x-b-form-group>

                <x-b-form-group name="foreign_currency[foreign_price]" title="金額（外幣）" required="true"
                                class="col-12 col-sm-4 mb-3 foreign_currency"
                                id="foreign_currency">
                    <input class="form-control @error('foreign[foreign_price]') is-invalid @enderror"
                           name="foreign_currency[foreign_price]"
                           required
                           type="number"
                           step="0.01"
                           value="{{ old('foreign_currency[foreign_price]', $data->foreign_currency ?? '') }}"/>
                </x-b-form-group>

                <label for="" class="form-label foreign_currency">科目
                    <span class="text-danger">*</span>
                </label>
                <select
                    name="foreign_currency[grade_id_fk]"
                    class="-select2 -single form-select col-12 col-sm-4 mb-3 foreign_currency @error('foreign_currency[grade_id_fk]') is-invalid @enderror"
                    required
                    data-placeholder="請選擇">
                    <option disabled selected value> -- select an option --</option>
                    @foreach($currencyDefault as $currencyData)
                        <option
                            value="{{ $currencyData['grade_id_fk'] }}">{{ $currencyData['code'] . ' ' . $currencyData['name'] }}
                        </option>
                    @endforeach
                </select>

                <label for="" class="form-label payable_account">科目
                    <span class="text-danger">*</span>
                </label>
                <select
                    name="payable_account[grade_id_fk]"
                    class="-select2 -single form-select col-12 col-sm-4 mb-3 payable_account @error('payable_account[grade_id_fk]') is-invalid @enderror"
                    required
                    data-placeholder="請選擇">
                    <option disabled selected value> -- select an option --</option>
                    @foreach($accountPayableDefault ?? [] as $accountPayableData)
                        <option
                            selected
                            value="{{ $accountPayableData['grade_id_fk'] }}">{{ $accountPayableData['code'] . ' ' . $accountPayableData['name'] }}
                        </option>
                    @endforeach
                </select>

                <label for="" class="form-label other">次科目
                    <span class="text-danger">*</span>
                </label>
                <select
                    name="other[grade_id_fk]"
                    class="-select2 -single form-select col-12 col-sm-4 mb-3 other"
                    required
                    data-placeholder="請選擇">
                    <option disabled selected value> -- select an option --</option>
                    @foreach($otherDefault as $otherData)
                        <option
                            value="{{ $otherData['grade_id_fk'] }}">{{ $otherData['code'] . ' ' . $otherData['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="card shadow p-4 mb-4">
                <h6>付款設定</h6>
{{--                <label class="form-label">付款狀態--}}
{{--                    <span class="text-danger">*</span>--}}
{{--                </label>--}}
{{--                <fieldset class="col-12 mb-4">--}}
{{--                    @foreach($paymentStatusList as $paymentStatus)--}}
{{--                        <div class="form-check form-check-inline">--}}
{{--                            <label class="form-check-label">--}}
{{--                                <input class="form-check-input"--}}
{{--                                       name="payable_status"--}}
{{--                                       required--}}
{{--                                       type="radio"--}}
{{--                                       value="{{ $paymentStatus['id'] }}">--}}
{{--                                {{ $paymentStatus['payment_status'] }}--}}
{{--                            </label>--}}
{{--                        </div>--}}
{{--                    @endforeach--}}
{{--                </fieldset>--}}
                <x-b-form-group name="payment_date" title="付款日期" required="true" class="col-12 col-sm-6">
                    <input class="form-control @error('payment_date') is-invalid @enderror"
                           name="payment_date"
                           required
                           type="date"
                           value="{{ old('payment_date', $data->payment_date ?? '') }}"/>
                </x-b-form-group>
                <x-b-form-group name="note" title="備註" required="false">
                    <input class="form-control @error('note') is-invalid @enderror"
                           name="note"
                           type="text"
                           value="{{ old('note', $data->note ?? '') }}"/>
                </x-b-form-group>
            </div>
            <div>
                <input type="hidden" name="pay_order_type" value="{{ request()->get('payOrdType') }}">
                <input type="hidden" name="pay_order_id" value="{{ request()->get('payOrdId') }}">
                <input type="hidden" name="is_final_payment" value="{{ request()->get('isFinalPay') }}">
                <input type="hidden" name="purchase_id" value="{{ request()->get('purchaseId') }}">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
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
            const currencyJson = @json($currencyDefault);

            const transactTypeEle = $('.transactType');

            //用來控制顯示「各付款方式」的element
            const cashEle = $('.cash');
            const chequeEle = $('.cheque');
            const remitEle = $('.remit');
            const foreignCurrencyEle = $('.foreign_currency');
            const accountPayableEle = $('.payable_account');
            const otherEle = $('.other');

            //元素：用來控制「各付款方式」是否傳送POST valueD
            const cashNameAttr = $('[name^=cash]');
            const chequeNameAttr = $('[name^=cheque]');
            const remitNameAttr = $('[name^=remit]');
            const foreignCurrencyNameAttr = $('[name^=foreign_currency]');
            const accountPayableNameAttr = $('[name^=payable_account]');
            const otherNameAttr = $('[name^=other]');

            const currencyRateEle = $('#rate');
            const currencyEle = $('[name^="foreign_currency[currency]"]');
            const foreignPriceEle = $('[name^="foreign_currency[foreign_price]"]');
            const twPriceEle = $('[name=tw_price]');

            $(document).ready(function () {
                cashEle.hide();
                chequeEle.hide();
                remitEle.hide();
                foreignCurrencyEle.hide();
                accountPayableEle.hide();
                otherEle.hide();

                cashNameAttr.prop('disabled', true);
                chequeNameAttr.prop('disabled', true);
                remitNameAttr.prop('disabled', true);
                foreignCurrencyNameAttr.prop('disabled', true);
                accountPayableNameAttr.prop('disabled', true);
                otherNameAttr.prop('disabled', true);
            })

            //選擇外幣後，自動帶入匯率、外幣金額、會計科目
            currencyEle.on('change', function () {
                currencyRateEle.val(currencyJson[currencyEle.val() - 1]['rate']);
                let foreignPrice = (twPriceEle.val() / currencyRateEle.val()).toFixed(2);
                foreignPriceEle.val(foreignPrice);
            });

            transactTypeEle.on('change', function () {
                dataType = $(this).attr('data-type');
                // 點擊「付款方式」任一選項後，先隱藏所有「付款方式」的元素
                cashEle.hide();
                chequeEle.hide();
                remitEle.hide();
                foreignCurrencyEle.hide();
                accountPayableEle.hide();
                otherEle.hide();

                // 點擊「付款方式」任一選項後，先disabled所有name屬性的元素付款方式
                cashNameAttr.prop('disabled', true);
                chequeNameAttr.prop('disabled', true);
                remitNameAttr.prop('disabled', true);
                foreignCurrencyNameAttr.prop('disabled', true);
                accountPayableNameAttr.prop('disabled', true);
                otherNameAttr.prop('disabled', true);

                //只顯示勾選到的「付款方式」元素、並傳送該name屬性value到後端
                switch (dataType) {
                    case 'Cash':
                        cashEle.show();
                        cashNameAttr.prop('disabled', false);
                        break;
                    case 'Cheque':
                        chequeEle.show();
                        chequeNameAttr.prop('disabled', false);
                        break;
                    case 'Remittance':
                        remitEle.show();
                        remitNameAttr.prop('disabled', false);
                        break;
                    case 'ForeignCurrency':
                        foreignCurrencyEle.show();
                        foreignCurrencyNameAttr.prop('disabled', false);
                        break;
                    case 'AccountsPayable':
                        accountPayableEle.show();
                        accountPayableNameAttr.prop('disabled', false);
                        break;
                    case 'Other':
                        otherEle.show();
                        otherNameAttr.prop('disabled', false);
                        break;
                    default:
                        cashEle.show();
                }
            });
        </script>
    @endpush
@endonce
