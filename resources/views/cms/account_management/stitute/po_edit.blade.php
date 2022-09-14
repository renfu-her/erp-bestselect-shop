@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">付款管理</h2>

    <form method="POST" action="{{ $form_action }}">
        @csrf
        <div class="card shadow p-4 mb-4">
            <h6>付款單明細</h6>

            @if($errors->any())
            <div class="alert alert-danger">{!! implode('', $errors->all('<div>:message</div>')) !!}</div>
            @endif

            <p class="fw-bold">支付對象：{{ $stitute_order->client_name }}</p>

            <div class="table-responsive tableOverBox border-bottom border-dark">
                <table class="table table-sm table-hover tableList mb-1">
                    <thead class="table-secondary align-middle">
                        <tr>
                            <td scope="col" class="text-wrap">
                                <div class="fw-bold text-nowrap">代墊單號</div>
                                <div>單據編號</div>
                            </td>
                            <th scope="col" style="min-width: 100px;">會計科目</th>
                            <th scope="col" style="min-width: 180px;">摘要</th>
                            <th scope="col" class="text-end">金額</th>
                            <th scope="col" class="text-end">數量</th>
                            <th scope="col" class="text-end">匯率</th>
                            <th scope="col">幣別</th>
                            <th scope="col" class="text-end">應付<br class="d-block d-lg-none">款項</th>
                            <th scope="col" class="text-end">已付<br class="d-block d-lg-none">款項</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td class="text-wrap">
                                <div class="fw-bold">{{ $stitute_order->sn }}</div>
                                <div>-</div>
                            </td>
                            <td class="text-wrap">{{ $stitute_grade->code }} {{ $stitute_grade->name }}</td>
                            <td class="text-wrap">{{ $stitute_order->summary }}</td>
                            <td class="text-end">${{ number_format($stitute_order->price, 2) }}</td>
                            <td class="text-end">{{ number_format($stitute_order->qty) }}</td>
                            <td class="text-end">{{ $currency->rate }}</td>
                            <td>{{ $currency->name }}</td>
                            <td class="text-end">${{ number_format($stitute_order->total_price) }}</td>
                            <td class="text-end"></td>
                        </tr>

                        @foreach($payable_data as $value)
                        <tr>
                            <td class="text-wrap">
                                <div class="fw-bold">-</div>
                                <div class="text-nowrap">{{ $value->po_sn }}</div>
                            </td>
                            <td class="text-wrap">{{ $value->account->code . ' ' . $value->account->name }}</td>
                            <td class="text-wrap">{{ $value->note }}</td>
                            <td class="text-end">${{ number_format($value->tw_price, 2) }}</td>
                            <td class="text-end">1</td>
                            <td class="text-end">{{ $value->currency_rate }}</td>
                            <td>{{ $value->currency_name }}</td>
                            <td class="text-end"></td>
                            <td class="text-end">${{ number_format($value->tw_price) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <table class="table table-sm tableList mb-0">
                <tfoot>
                    <tr>
                        <th scope="row" class="text-end">應付總計金額：${{ number_format($tw_price) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="card shadow p-4 mb-4">
            <h6>付款方式 <span class="text-danger">*</span></h6>

            <div class="row">
                <fieldset class="col-12 mb-3">
                    @php
                    $isFirst = true;
                    @endphp
                    @foreach($transactTypeList as $transactData)
                        <div class="form-check form-check-inline">
                            <label class="form-check-label transactType" data-type="{{ $transactData['key'] }}">
                                <input class="form-check-input" name="acc_transact_type_fk" type="radio" 
                                @if (count($payable_data) <= 0 && $isFirst)
                                    checked
                                @endif
                                {{ (count($payable_data) > 0 ? $payable_data->last()->acc_income_type_fk : 0) === $transactData['value'] ? 'checked' : ''}} 
                                value="{{ $transactData['value'] }}" required>
                                {{ $transactData['name'] }}
                            </label>
                        </div>
                        @php
                            $isFirst = false;
                        @endphp
                    @endforeach
                </fieldset>
                    
                <x-b-form-group title="金額（台幣）" required="true" class="col-12 col-sm-6 mb-3">
                    <input class="form-control @error('tw_price') is-invalid @enderror"
                            name="tw_price"
                            required
                            type="text" placeholder="請輸入台幣金額"
                            value="{{ old('tw_price', $tw_price ?? '') }}"/>
                </x-b-form-group>

                {{-- 現金 --}}
                <div class="col-12 col-sm-6 mb-3 cash">
                    <label class="form-label cash">會計科目
                        <span class="text-danger">*</span>
                    </label>
                    <select name="cash[grade_id_fk]" class="form-select -select2 -single cash @error('cash[grade_id_fk]') is-invalid @enderror" 
                        required data-placeholder="請選擇會計科目">
                        @php
                            $cash_first = true;
                        @endphp
                        @foreach($total_grades as $value)
                            @if(in_array($value['primary_id'], $cashDefault))
                            <option value="{{ $value['primary_id'] }}" {{ $cash_first ? 'selected' : '' }}>{{ $value['code'] . ' ' . $value['name'] }}</option>
                            @php
                                $cash_first = false;
                            @endphp
                            @endif
                        @endforeach
                    </select>
                    @error('cash[grade_id_fk]')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- 支票 --}}
                <div class="col-12 col-sm-6 mb-3 cheque d-none">
                    <label class="form-label cheque">會計科目 <span class="text-danger">*</span></label>
                    <select name="cheque[grade_id_fk]" class="form-select -select2 -single cheque @error('cheque[grade_id_fk]') is-invalid @enderror" 
                        required data-placeholder="請選擇會計科目">
                        @foreach($total_grades as $value)
                            @if(in_array($value['primary_id'], $chequeDefault))
                            <option value="{{ $value['primary_id'] }}" {{ $value['code'] == '2101' ? 'selected' : '' }}>{{ $value['code'] . ' ' . $value['name'] }}</option>
                            @endif
                        @endforeach
                    </select>
                    @error('cheque[grade_id_fk]')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 mb-3 cheque">
                    <label class="form-label cheque">支存銀行 <span class="text-danger">*</span></label>
                    <select name="cheque[grade_id]" class="form-select -select2 -single cheque @error('cheque[grade_id]') is-invalid @enderror" 
                        required data-placeholder="請選擇支存銀行">
                        @php
                            $cheque_first = true;
                        @endphp
                        @foreach($total_grades as $value)
                            @if(in_array($value['primary_id'], $chequeDefault))
                            <option value="{{ $value['primary_id'] }}" {{ $cheque_first ? 'selected' : '' }}>{{ $value['code'] . ' ' . $value['name'] }}</option>
                            @php
                                $cheque_first = false;
                            @endphp
                            @endif
                        @endforeach
                    </select>
                    @error('cheque[grade_id]')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <x-b-form-group name="cheque[ticket_number]" title="票號" required="true"
                                class="col-12 col-sm-6 mb-3 cheque"
                                id="ticket_number">
                    <input class="form-control @error('cheque[ticket_number]') is-invalid @enderror"
                            name="cheque[ticket_number]"
                            required
                            type="text" placeholder="請輸入票號"
                            value="{{ old('cheque[ticket_number]') }}"/>
                </x-b-form-group>
                <x-b-form-group name="cheque[due_date]"
                                title="到期日"
                                required="true"
                                class="col-12 col-sm-6 mb-3 cheque"
                                id="cheque[due_date]">
                    <input class="form-control @error('cheque[due_date]') is-invalid @enderror"
                            name="cheque[due_date]"
                            required
                            type="date"
                            value="{{ old('cheque[due_date]', date('Y-m-d', strtotime( date('Y-m-d'))) ) }}"/>
                </x-b-form-group>
                <x-b-form-group name="cheque[cashing_date]" title="兌現日" required="true"
                                class="col-12 col-sm-6 mb-3 cheque"
                                id="cheque[cashing_date]">
                    <input class="form-control @error('cheque[cashing_date]') is-invalid @enderror"
                            name="cheque[cashing_date]"
                            required
                            type="date"
                            value="{{ old('cheque[cashing_date]', date('Y-m-d', strtotime( date('Y-m-d'))) ) }}"/>
                </x-b-form-group>
                <div class="col-12 col-sm-6 mb-3 cheque">
                    <label class="form-label">狀態
                        <span class="text-danger">*</span>
                    </label>
                    <select name="cheque[status_code]" class="form-select @error('cheque[status_code]') is-invalid @enderror" 
                        aria-label="Select" required data-placeholder="請選擇狀態">
                        <option value="" selected disabled>請選擇狀態</option>
                        @foreach($chequeStatus as $c_key => $c_values)
                            <option value="{{ $c_key }}" {{ $c_key == 'paid' ? 'selected' : '' }}>{{ $c_values }}</option>
                        @endforeach
                    </select>
                    @error('cheque[status_code]')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- 匯款 --}}
                <div class="col-12 col-sm-6 mb-3 remit">
                    <label class="form-label remit">匯款銀行
                        <span class="text-danger">*</span>
                    </label>
                    <select name="remit[grade_id_fk]" class="form-select -select2 -single remit @error('remit[grade_id_fk]') is-invalid @enderror" 
                        required data-placeholder="請選擇匯款銀行">
                        @php
                            $remit_first = true;
                        @endphp
                        @foreach($total_grades as $value)
                            @if(in_array($value['primary_id'], $remitDefault))
                                <option value="{{ $value['primary_id'] }}" {{ $remit_first ? 'selected' : '' }}>{{ $value['code'] . ' ' . $value['name'] }}</option>
                                @php
                                    $remit_first = false;
                                @endphp
                            @endif
                        @endforeach
                    </select>
                    @error('remit[grade_id_fk]')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <x-b-form-group name="remit[remit_date]" title="匯款日期" required="true"
                                class="col-12 col-sm-6 mb-3 remit">
                    <input class="form-control @error('remit[remit_date]') is-invalid @enderror"
                            name="remit[remit_date]"
                            type="date"
                            required
                            value="{{ old('remit[remit_date]', date('Y-m-d', strtotime( date('Y-m-d'))) ) }}"/>
                </x-b-form-group>

                {{-- 外幣 --}}
                <div class="col-12 col-sm-6 mb-3 foreign_currency">
                    <label class="form-label foreign_currency">外幣
                        <span class="text-danger">*</span>
                    </label>
                    <select name="foreign_currency[currency]" required data-placeholder="請選擇外幣"
                        class="form-select -select2 -single foreign_currency @error('foreign_currency[currency]') is-invalid @enderror">
                        <option value="" selected disabled>請選擇</option>
                        @foreach($all_currency as $value)
                            <option value="{{ $value->currency_id }}">{{ $value->name }}</option>
                        @endforeach
                    </select>
                    @error('foreign_currency[currency]')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <x-b-form-group name="foreign_currency[rate]" title="匯率" required="true" class="col-12 col-sm-6 mb-3 foreign_currency">
                    <input class="form-control @error('foreign_currency[rate]') is-invalid @enderror"
                            name="foreign_currency[rate]"
                            required
                            id="rate"
                            type="number"
                            step="0.01" placeholder="請輸入匯率"
                            value="{{ old('foreign_currency[rate]') }}"/>
                </x-b-form-group>
                <x-b-form-group name="foreign_currency[foreign_price]" title="金額（外幣）" required="true"
                                class="col-12 col-sm-6 mb-3 foreign_currency"
                                id="foreign_currency">
                    <input class="form-control @error('foreign[foreign_price]') is-invalid @enderror"
                            name="foreign_currency[foreign_price]"
                            required
                            type="number"
                            step="0.01" placeholder="請輸入外幣金額"
                            value="{{ old('foreign_currency[foreign_price]') }}"/>
                </x-b-form-group>
                <div class="col-12 col-sm-6 mb-3 foreign_currency">
                    <label class="form-label foreign_currency">會計科目
                        <span class="text-danger">*</span>
                    </label>
                    <select name="foreign_currency[grade_id_fk]" class="form-select -select2 -single foreign_currency 
                        @error('foreign_currency[grade_id_fk]') is-invalid @enderror" required data-placeholder="請選擇會計科目">
                        @php
                            $foreign_first = true;
                        @endphp
                        @foreach($total_grades as $value)
                            @if(in_array($value['primary_id'], $currencyDefault))
                                <option value="{{ $value['primary_id'] }}" {{ $foreign_first ? 'selected' : '' }}>{{ $value['code'] . ' ' . $value['name'] }}</option>
                                @php
                                    $foreign_first = false;
                                @endphp
                            @endif
                        @endforeach
                    </select>
                    @error('foreign_currency[grade_id_fk]')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- 應付帳款 --}}
                <div class="col-12 col-sm-6 mb-3 payable_account">
                    <label class="form-label payable_account">會計科目
                        <span class="text-danger">*</span>
                    </label>
                    <select name="payable_account[grade_id_fk]" class="form-select -select2 -single payable_account 
                        @error('payable_account[grade_id_fk]') is-invalid @enderror" required data-placeholder="請選擇會計科目">
                        @php
                            $account_first = true;
                        @endphp
                        @foreach($total_grades ?? [] as $value)
                            @if(in_array($value['primary_id'], $accountPayableDefault))
                                <option value="{{ $value['primary_id'] }}" {{ $account_first ? 'selected' : '' }}>{{ $value['code'] . ' ' . $value['name'] }}</option>
                                @php
                                    $account_first = false;
                                @endphp
                            @endif
                        @endforeach
                    </select>
                    @error('payable_account[grade_id_fk]')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- 其他 --}}
                <div class="col-12 col-sm-6 mb-3 other">
                    <label class="form-label other">會計科目
                        <span class="text-danger">*</span>
                    </label>
                    <select name="other[grade_id_fk]" class="form-select -select2 -single other 
                        @error('other[grade_id_fk]') is-invalid @enderror" required data-placeholder="請選擇會計科目">
                        @php
                            $other_first = true;
                        @endphp
                        @foreach($total_grades as $otherData)
                            <option value="{{ $otherData['primary_id'] }}" {{ $other_first ? 'selected' : '' }}>{{ $otherData['code'] . ' ' . $otherData['name'] }}</option>
                            @php
                                $other_first = false;
                            @endphp
                        @endforeach
                    </select>
                    @error('other[grade_id_fk]')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card shadow p-4 mb-4">
            <h6>付款設定</h6>

            <div class="row">
                <x-b-form-group name="payment_date" title="付款日期" required="true" class="col-12 col-sm-6 mb-3">
                    <input class="form-control @error('payment_date') is-invalid @enderror" name="payment_date" required type="date" value="{{ old('payment_date', $payment_date ?? date('Y-m-d', strtotime( date('Y-m-d'))) ) }}"/>
                </x-b-form-group>
            </div>

            <div class="row">
                <x-b-form-group name="summary" title="摘要" required="false" class="col-12 col-sm-6 mb-3">
                    <input class="form-control @error('summary') is-invalid @enderror" name="summary" type="text" value="{{ old('summary', '') }}">
                </x-b-form-group>
                <x-b-form-group name="note" title="備註" required="false" class="col-12 col-sm-6 mb-3">
                    <input class="form-control @error('note') is-invalid @enderror" name="note" type="text" value="{{ old('note', '') }}"/>
                </x-b-form-group>
            </div>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
            <a href="{{ $previous_url }}" class="btn btn-outline-primary px-4" role="button">返回上一頁</a>
        </div>
    </form>
@endsection

@once
    @push('sub-styles')
        <style>
            .cash,
            .cheque,
            .remit,
            .foreign_currency,
            .payable_account,
            .other {
                display: none;
            }
            .tableList > :not(caption) > * > * {
                line-height: initial;
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            const currencyJson = @json($all_currency);

            const transactTypeEle = $('.transactType');
            const transactTypeSelectedRadioEle = $('.transactType input:checked');

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

            //付款方式ID數值
            const CASH = {{ \App\Enums\Supplier\Payment::Cash }};
            const CHEQUE = {{ \App\Enums\Supplier\Payment::Cheque }};
            const REMIT = {{ \App\Enums\Supplier\Payment::Remittance }};
            const FOREIGN_CURRENCY = {{ \App\Enums\Supplier\Payment::ForeignCurrency }};
            const ACCOUNTS_PAYABLE = {{ \App\Enums\Supplier\Payment::AccountsPayable }};
            const OTHER = {{ \App\Enums\Supplier\Payment::Other }};

            $(document).ready(function () {
                let selectedType = transactTypeSelectedRadioEle.val();

                //初次新create建立，只顯示現金畫面
                if (selectedType === undefined) {
                    // cashEle.hide();
                    chequeEle.hide();
                    remitEle.hide();
                    foreignCurrencyEle.hide();
                    accountPayableEle.hide();
                    otherEle.hide();

                    // cashNameAttr.prop('disabled', true);
                    chequeNameAttr.prop('disabled', true);
                    remitNameAttr.prop('disabled', true);
                    foreignCurrencyNameAttr.prop('disabled', true);
                    accountPayableNameAttr.prop('disabled', true);
                    otherNameAttr.prop('disabled', true);
                } else {
                    //資料庫已經有記錄，先隱藏所有選項
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
                }

                // 只顯示出資料庫有的「付款方式」
                switch (parseInt(selectedType, 10)) {
                    case CASH:
                        cashEle.show();
                        cashNameAttr.prop('disabled', false);
                        break;
                    case CHEQUE:
                        chequeEle.show();
                        chequeNameAttr.prop('disabled', false);
                        break;
                    case REMIT:
                        remitEle.show();
                        remitNameAttr.prop('disabled', false);
                        break;
                    case FOREIGN_CURRENCY:
                        foreignCurrencyEle.show();
                        foreignCurrencyNameAttr.prop('disabled', false);
                        break;
                    case ACCOUNTS_PAYABLE:
                        accountPayableEle.show();
                        accountPayableNameAttr.prop('disabled', false);
                        break;
                    case OTHER:
                        otherEle.show();
                        otherNameAttr.prop('disabled', false);
                        break;
                    default:
                        cashEle.show();
                        cashNameAttr.prop('disabled', false);
                }
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

            $('form').submit(function(e) {
                if($('input[name="acc_transact_type_fk"]:checked').val() == '2'){
                    const reg = new RegExp(/^[A-Z]{2}[0-9]{7}$/);
                    if(! reg.test($('input[name="cheque[ticket_number]"]').val())){
                        $('input[name="cheque[ticket_number]"]').addClass('is-invalid');
                        e.preventDefault();
                        return false;
                    }
                }
            });
        </script>
    @endpush
@endonce
