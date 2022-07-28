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
        <a href="{{ Route('cms.delivery.return-pay-order', ['id' => $delivery->delivery_id]) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>

    <form method="POST" action="{{ $form_action }}">
        @csrf
        <div class="row justify-content-end mb-4">
            <h2 class="mb-4">付款管理</h2>
            <div class="card shadow p-4 mb-4">
                <div class="card-body">
                    <div class="col">
                        <dl class="row mb-0">
                            <dt>支付對象：{{ $delivery->buyer_name }}</dt>
                        </dl>
                    </div>
                </div>

                <div class="table-responsive tableOverBox">
                    <table class="table table-hover table-bordered tableList mb-0">
                        <thead>
                            <tr>
                                <th scope="col">付款單號</th>
                                <th scope="col">單據編號</th>
                                <th scope="col">會計科目</th>
                                <th scope="col">摘要</th>
                                <th scope="col">金額</th>
                                <th scope="col">數量</th>
                                <th scope="col">匯率</th>
                                <th scope="col">幣別</th>
                                <th scope="col">應付款項</th>
                                <th scope="col">已付款項</th>
                            </tr>
                        </thead>

                        <tbody class="product_list">
                            @foreach($delivery->delivery_back_items as $db_value)
                            <tr>
                                <td>{{ $delivery->po_sn }}</td>
                                <td>{{ $delivery->sub_order_sn }}</td>
                                <td>{{ $product_grade_name }}</td>
                                <td>{{ $db_value->product_title }}{{'（' . $delivery->sub_order_ship_event . ' - ' . $delivery->sub_order_ship_category_name . '）'}}{{'（' . $db_value->price . ' * ' . $db_value->qty . '）'}}</td>
                                <td class="text-end">{{ number_format($db_value->price, 2) }}</td>
                                <td class="text-end">{{$db_value->qty}}</td>
                                <td class="text-end">{{ $currency->rate }}</td>
                                <td>{{ $currency->name }}</td>
                                <td class="text-end">{{ number_format($db_value->total_price) }}</td>
                                <td class="text-end"></td>
                            </tr>
                            @endforeach

                            {{--
                            @if($delivery->dlv_fee > 0)
                            <tr>
                                <td>{{ $delivery->po_sn }}</td>
                                <td></td>
                                <td>{{ $logistics_grade_name }}</td>
                                <td>物流費用</td>
                                <td class="text-end">{{ number_format($delivery->dlv_fee, 2) }}</td>
                                <td class="text-end">1</td>
                                <td class="text-end">1</td>
                                <td>NTD</td>
                                <td class="text-end">{{ number_format($delivery->dlv_fee) }}</td>
                                <td class="text-end"></td>
                            </tr>
                            @endif

                            @if($delivery->discount_value > 0)
                                @foreach($order_discount ?? [] as $d_value)
                                <tr>
                                    <td>{{ $delivery->po_sn }}</td>
                                    <td></td>
                                    <td>{{ $d_value->account_code }} {{ $d_value->account_name }}</td>
                                    <td>{{ $d_value->title }}</td>
                                    <td class="text-end">-{{ number_format($d_value->discount_value, 2) }}</td>
                                    <td class="text-end">1</td>
                                    <td class="text-end">1</td>
                                    <td>NTD</td>
                                    <td class="text-end">-{{ number_format($d_value->discount_value) }}</td>
                                    <td class="text-end"></td>
                                </tr>
                                @endforeach
                            @endif
                            --}}

                            @foreach($payable_data as $value)
                            <tr>
                                <td>{{ $delivery->po_sn }}</td>
                                <td></td>
                                <td>{{ $value->account->code . ' ' . $value->account->name }}</td>
                                <td>{{ $value->payable_method_name . ' - ' . $value->summary }}</td>
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
                                <th scope="row" colspan="10" class="text-end">應付總計金額：{{ number_format($tw_price) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="card shadow p-4 mb-4">
                <fieldset class="col-12 mb-4 ">
                    <h6>付款方式
                        <span class="text-danger">*</span>
                    </h6>
                    @foreach($transactTypeList as $transactData)
                        <div class="form-check form-check-inline">
                            <label class="form-check-label transactType" data-type="{{ $transactData['key'] }}">
                                <input class="form-check-input" name="acc_transact_type_fk" type="radio" {{ ( count($payable_data) > 0 ? $payable_data->last()->acc_income_type_fk : 0) === $transactData['value'] ? 'checked' : ''}} value="{{ $transactData['value'] }}" required>
                                {{ $transactData['name'] }}
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

                <div class="col-12 col-sm-4 mb-3 cash">
                    <label for="" class="form-label cash">會計科目
                        <span class="text-danger">*</span>
                    </label>
                    <select name="cash[grade_id_fk]" class="form-select -select2 -single cash @error('cash[grade_id_fk]') is-invalid @enderror" required data-placeholder="請選擇會計科目">
                        <option value="" selected disabled>請選擇</option>
                        @foreach($total_grades as $value)
                            @if(in_array($value['primary_id'], $cashDefault))
                            <option value="{{ $value['primary_id'] }}">{{ $value['code'] . ' ' . $value['name'] }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-4 mb-3 cheque">
                    <label for="" class="form-label cheque">支存銀行
                        <span class="text-danger">*</span>
                    </label>
                    <select name="cheque[grade_id_fk]" class="form-select -select2 -single cheque @error('cheque[grade_id_fk]') is-invalid @enderror" required data-placeholder="請選擇支存銀行">
                        <option value="" selected disabled>請選擇</option>
                        @foreach($total_grades as $value)
                            @if(in_array($value['primary_id'], $chequeDefault))
                            <option value="{{ $value['primary_id'] }}">{{ $value['code'] . ' ' . $value['name'] }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <x-b-form-group name="cheque[check_num]" title="票號" required="true"
                                class="col-12 col-sm-4 mb-3 cheque"
                                id="check_num">
                    <input class="form-control @error('cheque[check_num]') is-invalid @enderror"
                           name="cheque[check_num]"
                           required
                           type="text"
                           value="{{ old('cheque[check_num]') }}"/>
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
                           value="{{ old('cheque[maturity_date]', date('Y-m-d', strtotime( date('Y-m-d'))) ) }}"/>
                </x-b-form-group>
                <x-b-form-group name="cheque[cash_cheque_date]" title="兌現日" required="true"
                                class="col-12 col-sm-4 mb-3 cheque"
                                id="cheque[cash_cheque_date]">
                    <input class="form-control @error('cheque[cash_cheque_date]') is-invalid @enderror"
                           name="cheque[cash_cheque_date]"
                           required
                           type="date"
                           value="{{ old('cheque[cash_cheque_date]', date('Y-m-d', strtotime( date('Y-m-d'))) ) }}"/>
                </x-b-form-group>
                <div class="col-12 col-sm-4 mb-3 cheque">
                    <label class="form-label">狀態
                        <span class="text-danger">*</span>
                    </label>
                    <select name="cheque[cheque_status]" class="form-select" aria-label="Select" required data-placeholder="請選擇狀態">
                        <option value="" selected disabled>請選擇狀態</option>
                        @foreach($chequeStatus as $chequeData)
                            <option value="{{ $chequeData['id'] }}">
                                {{ $chequeData['status'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-4 mb-3 remit">
                    <label for="" class="form-label remit">匯款銀行
                        <span class="text-danger">*</span>
                    </label>
                    <select name="remit[grade_id_fk]" class="form-select -select2 -single remit @error('remit[grade_id_fk]') is-invalid @enderror" required data-placeholder="請選擇匯款銀行">
                        <option value="" selected disabled>請選擇</option>
                        @foreach($total_grades as $value)
                            @if(in_array($value['primary_id'], $remitDefault))
                                <option value="{{ $value['primary_id'] }}">{{ $value['code'] . ' ' . $value['name'] }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <x-b-form-group name="remit[remit_date]" title="匯款日期" required="true"
                                class="col-12 col-sm-4 mb-3 remit">
                    <input class="form-control @error('remit[remit_date]') is-invalid @enderror"
                           name="remit[remit_date]"
                           type="date"
                           required
                           value="{{ old('remit[remit_date]', date('Y-m-d', strtotime( date('Y-m-d'))) ) }}"/>
                </x-b-form-group>

                <div class="col-12 col-sm-4 mb-3 foreign_currency">
                    <label for="" class="form-label foreign_currency">外幣
                        <span class="text-danger">*</span>
                    </label>
                    <select name="foreign_currency[currency]" class="form-select -select2 -single foreign_currency @error('foreign_currency[currency]') is-invalid @enderror" required data-placeholder="請選擇外幣">
                        <option value="" selected disabled>請選擇</option>
                        @foreach($all_currency as $value)
                            <option value="{{ $value->currency_id }}">{{ $value->name }}</option>
                        @endforeach
                    </select>
                </div>

                <x-b-form-group name="foreign_currency[rate]" title="匯率" required="true" class="col-12 col-sm-4 mb-3 foreign_currency">
                    <input class="form-control @error('foreign_currency[rate]') is-invalid @enderror"
                           name="foreign_currency[rate]"
                           required
                           id="rate"
                           type="number"
                           step="0.01"
                           value="{{ old('foreign_currency[rate]') }}"/>
                </x-b-form-group>

                <x-b-form-group name="foreign_currency[foreign_price]" title="金額（外幣）" required="true"
                                class="col-12 col-sm-4 mb-3 foreign_currency"
                                id="foreign_currency">
                    <input class="form-control @error('foreign[foreign_price]') is-invalid @enderror"
                           name="foreign_currency[foreign_price]"
                           required
                           type="number"
                           step="0.01"
                           value="{{ old('foreign_currency[foreign_price]') }}"/>
                </x-b-form-group>

                <div class="col-12 col-sm-4 mb-3 foreign_currency">
                    <label for="" class="form-label foreign_currency">會計科目
                        <span class="text-danger">*</span>
                    </label>
                    <select name="foreign_currency[grade_id_fk]" class="form-select -select2 -single foreign_currency @error('foreign_currency[grade_id_fk]') is-invalid @enderror" required data-placeholder="請選擇會計科目">
                        <option value="" selected disabled>請選擇</option>
                        @foreach($total_grades as $value)
                            @if(in_array($value['primary_id'], $currencyDefault))
                                <option value="{{ $value['primary_id'] }}">{{ $value['code'] . ' ' . $value['name'] }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-4 mb-3 payable_account">
                    <label for="" class="form-label payable_account">會計科目
                        <span class="text-danger">*</span>
                    </label>
                    <select name="payable_account[grade_id_fk]" class="form-select -select2 -single payable_account @error('payable_account[grade_id_fk]') is-invalid @enderror" required data-placeholder="請選擇會計科目">
                        <option value="" selected disabled>請選擇</option>
                        @foreach($total_grades ?? [] as $value)
                            @if(in_array($value['primary_id'], $accountPayableDefault))
                                <option value="{{ $value['primary_id'] }}">{{ $value['code'] . ' ' . $value['name'] }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-4 mb-3 other">
                    <label for="" class="form-label other">會計科目
                        <span class="text-danger">*</span>
                    </label>
                    <select name="other[grade_id_fk]" class="form-select -select2 -single other @error('other[grade_id_fk]') is-invalid @enderror" required data-placeholder="請選擇會計科目">
                        <option value="" selected disabled>請選擇</option>
                        @foreach($total_grades as $otherData)
                            <option value="{{ $otherData['primary_id'] }}">{{ $otherData['code'] . ' ' . $otherData['name'] }}</option>
                        @endforeach
                    </select>
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
                        <input class="form-control @error('note') is-invalid @enderror" name="note" type="text" value="{{ old('note', (count($payable_data) > 0 ? $payable_data->last()->note : '')) }}"/>
                    </x-b-form-group>
                </div>
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
        </script>
    @endpush
@endonce
