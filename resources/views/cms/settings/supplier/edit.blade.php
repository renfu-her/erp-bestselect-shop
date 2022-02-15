@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.supplier.index', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>

    <form method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf

        <div class="card mb-4">
            <div class="card-header">
                @if ($method === 'create') 新增 @else 編輯 @endif 廠商
            </div>
            <div class="card-body">
                <div class="row">
                    <x-b-form-group name="name" title="廠商名稱" required="true" class="col-12 col-sm-6">
                        <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', (isset($supplierData)?($supplierData->name ?? ''): '')) }}" />
                    </x-b-form-group>
                    <x-b-form-group name="nickname" title="廠商簡稱" required="true" class="col-12 col-sm-6">
                        <input class="form-control @error('nickname') is-invalid @enderror" name="nickname" value="{{ old('nickname', (isset($supplierData)?($supplierData->nickname ?? ''): '')) }}" />
                    </x-b-form-group>
                    <x-b-form-group name="vat_no" title="統一編號" required="true" class="col-12 col-sm-6">
                        <input class="form-control @error('vat_no') is-invalid @enderror" name="vat_no" value="{{ old('vat_no', (isset($supplierData)?($supplierData->vat_no ?? ''): '')) }}" />
                    </x-b-form-group>
                    <x-b-form-group name="postal_code" title="公司郵遞區號" required="true" class="col-12 col-sm-6">
                        <input class="form-control @error('postal_code') is-invalid @enderror" name="postal_code" value="{{ old('postal_code', (isset($supplierData)?($supplierData->postal_code ?? ''): '')) }}" />
                    </x-b-form-group>
                    <x-b-form-group name="contact_address" title="公司地址" required="false">
                        <input class="form-control @error('contact_address') is-invalid @enderror" name="contact_address" value="{{ old('contact_address', (isset($supplierData)?($supplierData->contact_address ?? ''): '')) }}" />
                    </x-b-form-group>
                    <x-b-form-group name="contact_person" title="訂單聯絡人" required="true" class="col-12 col-sm-6">
                        <input class="form-control @error('contact_person') is-invalid @enderror" name="contact_person" value="{{ old('contact_person', (isset($supplierData)?($supplierData->contact_person ?? ''): '')) }}" />
                    </x-b-form-group>
                    <x-b-form-group name="job" title="職稱" required="true" class="col-12 col-sm-6">
                        <input class="form-control @error('job') is-invalid @enderror" name="job" value="{{ old('job', (isset($supplierData)?($supplierData->job ?? ''): '')) }}" />
                    </x-b-form-group>
                    <x-b-form-group name="contact_tel" title="公司電話" required="true" class="col-12 col-sm-6">
                        <input class="form-control @error('contact_tel') is-invalid @enderror" name="contact_tel" value="{{ old('contact_tel', (isset($supplierData)?($supplierData->contact_tel ?? ''): '')) }}" />
                    </x-b-form-group>
                    <x-b-form-group name="extension" title="分機" required="false" class="col-12 col-sm-6">
                        <input class="form-control @error('extension') is-invalid @enderror" name="extension" value="{{ old('extension', (isset($supplierData)?($supplierData->extension ?? ''): '')) }}" />
                    </x-b-form-group>
                    <x-b-form-group name="fax" title="公司傳真" required="false" class="col-12 col-sm-6">
                        <input class="form-control @error('fax') is-invalid @enderror" name="fax" value="{{ old('fax', (isset($supplierData)?($supplierData->fax ?? ''): '')) }}" />
                    </x-b-form-group>
                    <x-b-form-group name="mobile_line" title="手機或Line" required="true" class="col-12 col-sm-6">
                        <input class="form-control @error('mobile_line') is-invalid @enderror" name="mobile_line" value="{{ old('mobile_line', (isset($supplierData)?($supplierData->mobile_line ?? ''): '')) }}" />
                    </x-b-form-group>
                    <x-b-form-group name="email" title="電子信箱" required="false" class="col-12 col-sm-6">
                        <input class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', (isset($supplierData)?($supplierData->email ?? ''): '')) }}" />
                    </x-b-form-group>
                    <x-b-form-group name="memo" title="備註" required="false">
                        <input class="form-control @error('memo') is-invalid @enderror" name="memo" value="{{ old('memo', (isset($supplierData)?($supplierData->memo ?? ''): '')) }}" />
                    </x-b-form-group>
                </div>

                @if ($method === 'edit')
                    <input type='hidden' name='id' value="{{ old('id', $id) }}" />
                @endif
                @error('id')
                <div class="alert alert-danger mt-3">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                發票資訊
            </div>
            <div class="card-body">
                <div class="row">
                <x-b-form-group name="invoice_address" title="發票地址" required="false" class="col-12 col-sm-6">
                    <input class="form-control @error('invoice_address') is-invalid @enderror" name="invoice_address" value="{{ old('invoice_address', (isset($supplierData)?($supplierData->invoice_address ?? ''): '')) }}" />
                </x-b-form-group>
                <x-b-form-group name="invoice_postal_code" title="發票郵遞區號" required="false" class="col-12 col-sm-6">
                    <input class="form-control @error('invoice_postal_code') is-invalid @enderror" name="invoice_postal_code" value="{{ old('invoice_postal_code', (isset($supplierData)?($supplierData->invoice_postal_code ?? ''): '')) }}" />
                </x-b-form-group>
                <x-b-form-group name="invoice_recipient" title="發票收件人" required="false" class="col-12 col-sm-6">
                    <input class="form-control @error('invoice_recipient') is-invalid @enderror" name="invoice_recipient" value="{{ old('invoice_recipient', (isset($supplierData)?($supplierData->invoice_recipient ?? ''): '')) }}" />
                </x-b-form-group>
                <x-b-form-group name="invoice_phone" title="發票收件人電話" required="false" class="col-12 col-sm-6">
                    <input class="form-control @error('invoice_phone') is-invalid @enderror" name="invoice_phone" value="{{ old('invoice_phone', (isset($supplierData)?($supplierData->invoice_phone ?? ''): '')) }}" />
                </x-b-form-group>
                    <fieldset class="col-12 mb-3">
                        <legend class="col-form-label p-0 mb-2">發票寄送日<span class="text-danger">*</span></legend>
                        <div class="px-1 pt-1">
                            <div class="form-check form-check-inline">
                                <label class="form-check-label" data-type="月底前">
                                    <input class="form-check-input" name="invoice_date_fk" value="1" type="radio"
                                           required
                                           @if('1' == old('invoice_date_fk', (isset($supplierData)?($supplierData->invoice_date_fk ?? true): true))) checked @endif>
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <label class="form-check-label" data-type="次月（幾日前）">
                                    <input class="form-check-input" name="invoice_date_fk" value="2" type="radio"
                                           required
                                           @if('2' == old('invoice_date_fk', (isset($supplierData)?($supplierData->invoice_date_fk ?? ''): ''))) checked @endif>
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <label class="form-check-label" data-type="其它">
                                    <input class="form-check-input" name="invoice_date_fk" value="3" type="radio"
                                           required
                                           @if('3' == old('invoice_date_fk', (isset($supplierData)?($supplierData->invoice_date_fk ?? ''): ''))) checked @endif>
                                </label>
                            </div>
                            <div class="col-12 col-sm-4 mb-3 invoice_date">
                                <select class="form-select" aria-label="Select" name="invoice_date">
                                    @for($startDate = 1; $startDate <= 31; $startDate++)
                                        <option
                                            @if (old('invoice_date', (isset($supplierData)?
                                                                                                (($supplierData->invoice_date == $startDate) ? true : false)
                                                                                                : false)))
                                            selected
                                            @endif
                                            value="{{ $startDate }}">{{ $startDate }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <input type="text"
                                   class="form-control invoice_date_other @error('invoice_date_other') is-invalid @enderror"
                                   name="invoice_date_other"
                                   value="{{ old('invoice_date_other', (isset($supplierData)?($supplierData->invoice_date_other ?? ''): '')) }}"/>
                        </div>
                    </fieldset>

                    <fieldset class="col-12 mb-3">
                        <legend class="col-form-label p-0 mb-2">發票寄送方式<span class="text-danger">*</span></legend>
                        <div class="px-1 pt-1">
                            <div class="form-check form-check-inline">
                                <label class="form-check-label" data-type="郵寄出紙本">
                                    <input class="form-check-input" name="invoice_ship_fk" value="1" type="radio" required @if('1' == old('invoice_ship_fk', (isset($supplierData)?($supplierData->invoice_ship_fk ?? true): true))) checked @endif>
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <label class="form-check-label" data-type="電子檔(不再寄出紙本)收發票Email">
                                    <input class="form-check-input" name="invoice_ship_fk" value="2" type="radio" required @if('2' == old('invoice_ship_fk', (isset($supplierData)?($supplierData->invoice_ship_fk ?? ''): ''))) checked @endif>
                                </label>
                            </div>
                                <input class="form-control invoice_email @error('invoice_email') is-invalid @enderror"
                                       type="text"
                                       name="invoice_email"
                                       value="{{ old('invoice_email', (isset($supplierData)?($supplierData->invoice_email ?? ''): '')) }}"/>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                收貨資訊
            </div>
            <div class="card-body">
                <div class="row">
                    <x-b-form-group name="shipping_address" title="收貨地址" required="false" class="col-12 col-sm-6">
                        <input class="form-control @error('shipping_address') is-invalid @enderror" name="shipping_address" value="{{ old('shipping_address', (isset($supplierData)?($supplierData->shipping_address ?? ''): '')) }}" />
                    </x-b-form-group>
                    <x-b-form-group name="shipping_postal_code" title="收貨郵遞區號" required="false" class="col-12 col-sm-6">
                        <input class="form-control @error('shipping_postal_code') is-invalid @enderror" name="shipping_postal_code" value="{{ old('shipping_postal_code', (isset($supplierData)?($supplierData->shipping_postal_code ?? ''): '')) }}" />
                    </x-b-form-group>
                    <x-b-form-group name="shipping_recipient" title="收貨聯絡人" required="false" class="col-12 col-sm-6">
                        <input class="form-control @error('shipping_recipient') is-invalid @enderror" name="shipping_recipient" value="{{ old('shipping_recipient', (isset($supplierData)?($supplierData->shipping_recipient ?? ''): '')) }}" />
                    </x-b-form-group>
                    <x-b-form-group name="shipping_phone" title="收貨聯絡人電話" required="false" class="col-12 col-sm-6">
                        <input class="form-control @error('shipping_phone') is-invalid @enderror" name="shipping_phone" value="{{ old('shipping_phone', (isset($supplierData)?($supplierData->shipping_phone ?? ''): '')) }}" />
                    </x-b-form-group>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                其它
            </div>
            <div class="card-body">
                <div class="row">
                    <fieldset class="col-12 mb-3">
                        <legend class="col-form-label p-0 mb-2">結帳日<span class="text-danger">*</span></legend>
                        <div class="px-1 pt-1">
                            <div class="form-check form-check-inline">
                                <label class="form-check-label" data-type="幾號">
                                    <input class="form-check-input" name="account_fk" value="1" type="radio"
                                           required
                                           @if('1' == old('account_fk', (isset($supplierData)?($supplierData->account_fk ?? true): true))) checked @endif>
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <label class="form-check-label" data-type="月底">
                                    <input class="form-check-input" name="account_fk" value="2" type="radio"
                                           required
                                           @if('2' == old('account_fk', (isset($supplierData)?($supplierData->account_fk ?? ''): ''))) checked @endif>
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <label class="form-check-label" data-type="其它">
                                    <input class="form-check-input" name="account_fk" value="3" type="radio"
                                           required
                                           @if('3' == old('account_fk', (isset($supplierData)?($supplierData->account_fk ?? ''): ''))) checked @endif>
                                </label>
                            </div>
                            <div class="col-12 col-sm-4 mb-3 account_date">
                                <select class="form-select" aria-label="Select" name="account_date">
                                    @for($startDate = 1; $startDate <= 31; $startDate++)
                                        <option
                                            @if (old('account_date', (isset($supplierData)?
                                                                                                (($supplierData->account_date == $startDate) ? true : false)
                                                                                                : false)))
                                            selected
                                            @endif
                                            value="{{ $startDate }}">{{ $startDate }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <input type="text"
                                   class="form-control account_date_other @error('account_date_other') is-invalid @enderror"
                                   name="account_date_other"
                                   value="{{ old('account_date_other', (isset($supplierData)?($supplierData->account_date_other ?? ''): '')) }}"/>
                        </div>
                    </fieldset>
                    <div class="col-12 mb-3">
                        <label class="form-label">付款日</label>
                        <div class="input-group has-validation">
                            <input type="date" class="form-control -startDate @error('pay_date') is-invalid @enderror"
                                   name="pay_date" value="{{ old('pay_date', (isset($supplierData)?($supplierData->pay_date ? explode(' ', $supplierData->pay_date)[0] : ''): '')) }}" aria-label="付款日" />
                            <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                            <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                            <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                            <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                            <button class="btn" data-daysBefore="month" type="button">本月</button>
                            <div class="invalid-feedback">
                                @error('pay_date')
                                {{ $message }}
                                @enderror
                            </div>
                        </div>
                    </div>

                    <x-b-form-group name="request_data" title="請款資料" required="false">
                        <input class="form-control @error('request_data') is-invalid @enderror" name="request_data" value="{{ old('request_data', (isset($supplierData)?($supplierData->request_data ?? ''): '')) }}" />
                    </x-b-form-group>

                    <fieldset class="col-12 mb-3">
                        <legend class="col-form-label p-0 mb-2">是否配合喜鴻物流？<span class="text-danger">*</span></legend>
                        <div class="px-1 pt-1">
                            <div class="form-check form-check-inline">
                                <label class="form-check-label" data-type="未洽談">
                                    <input class="form-check-input" name="shipping_method_fk" value="1" type="radio" required @if('1' == old('shipping_method_fk', (isset($supplierData)?($supplierData->shipping_method_fk ?? true): true))) checked @endif>
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <label class="form-check-label" data-type="洽談中">
                                    <input class="form-check-input" name="shipping_method_fk" value="2" type="radio" required @if('2' == old('shipping_method_fk', (isset($supplierData)?($supplierData->shipping_method_fk ?? ''): ''))) checked @endif>
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <label class="form-check-label" data-type="是">
                                    <input class="form-check-input" name="shipping_method_fk" value="3" type="radio" required @if('3' == old('shipping_method_fk', (isset($supplierData)?($supplierData->shipping_method_fk ?? ''): ''))) checked @endif>
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <label class="form-check-label" data-type="否">
                                    <input class="form-check-input" name="shipping_method_fk" value="4" type="radio" required @if('4' == old('shipping_method_fk', (isset($supplierData)?($supplierData->shipping_method_fk ?? ''): ''))) checked @endif>
                                </label>
                            </div>
                        </div>
                    </fieldset>

                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                付款資訊
            </div>
            <div class="card-body">
                <fieldset class="col-12 mb-3">
                    <legend class="col-form-label p-0 mb-2">付款方式（可複選）<span class="text-danger">*</span></legend>
                    <div class="px-1 pt-1">
                        <div class="form-check mb-3">
                            <label class="form-check-label" data-type="現金">
                                <input class="form-check-input" name="paytype[]" value="0" type="checkbox" @if('0' == old('paytype.0', '') || true == in_array('0', $payTypeList ?? [])) checked @endif>
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <label class="form-check-label" data-type="外幣">
                                <input class="form-check-input" name="paytype[]" value="3" type="checkbox" @if('3' == old('paytype.1', '') || true == in_array('3', $payTypeList ?? [])) checked @endif>
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <label class="form-check-label" data-type="匯款">
                                <input class="form-check-input" name="paytype[]" value="2" type="checkbox" @if('2' == old('paytype.2', '') || true == in_array('2', $payTypeList ?? [])) checked @endif>
                            </label>
                            <div class="row">
                                @php
                                    $remittanceData = null;
                                    foreach ($payList ?? [] as $key => $value) {
                                        if ('2' == $value['type']) {
                                            $remittanceData = $value;
                                            break;
                                        }
                                    }
                                @endphp
                                <x-b-form-group name="bank_cname" title="匯款銀行" required="true" class="col-12 col-sm-6">
                                    <input class="form-control @error('bank_cname') is-invalid @enderror" name="bank_cname" value="{{ old('bank_cname', $remittanceData['bank_cname'] ?? '') }}" />
                                </x-b-form-group>
                                <x-b-form-group name="bank_code" title="匯款銀行代碼" required="true" class="col-12 col-sm-6">
                                    <input class="form-control @error('bank_code') is-invalid @enderror" name="bank_code" value="{{ old('bank_code', $remittanceData['bank_code'] ?? '') }}" />
                                </x-b-form-group>
                                <x-b-form-group name="bank_acount" title="匯款戶名" required="true" class="col-12 col-sm-6">
                                    <input class="form-control @error('bank_acount') is-invalid @enderror" name="bank_acount" value="{{ old('bank_acount', $remittanceData['bank_acount'] ?? '') }}" />
                                </x-b-form-group>
                                <x-b-form-group name="bank_numer" title="匯款帳號" required="true" class="col-12 col-sm-6">
                                    <input class="form-control @error('bank_numer') is-invalid @enderror" name="bank_numer" value="{{ old('bank_numer', $remittanceData['bank_numer'] ?? '') }}" />
                                </x-b-form-group>
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <label class="form-check-label" data-type="支票">
                                <input class="form-check-input" name="paytype[]" value="1" type="checkbox" @if('1' == old('paytype.3', '') || true == in_array('1', $payTypeList ?? [])) checked @endif>
                            </label>
                            <div class="row">
                                @php
                                    $chequeData = null;
                                    foreach ($payList ?? [] as $key => $value) {
                                        if ('1' == $value['type']) {
                                            $chequeData = $value;
                                            break;
                                        }
                                    }
                                @endphp
                                <x-b-form-group name="cheque_payable" title="支票抬頭" required="true" class="col-12">
                                    <input class="form-control @error('cheque_payable') is-invalid @enderror" name="cheque_payable" value="{{ old('cheque_payable', $chequeData['cheque_payable'] ?? '') }}" />
                                </x-b-form-group>
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <label class="form-check-label" data-type="其他">
                                <input class="form-check-input" name="paytype[]" value="5" type="checkbox" @if('5' == old('paytype.4', '') || true == in_array('5', $payTypeList ?? [])) checked @endif>
                            </label>
                        </div>
                    </div>
                </fieldset>
                <fieldset class="col-12 mb-3">
                    <legend class="col-form-label p-0 mb-2">預設付款方式<span class="text-danger">*</span></legend>
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline">
                            <label class="form-check-label" data-type="現金">
                                <input class="form-check-input" name="def_paytype" value="0" type="radio" required @if('0' == old('def_paytype', (isset($supplierData)?($supplierData->def_paytype ?? ''): ''))) checked @endif>
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label" data-type="外幣">
                                <input class="form-check-input" name="def_paytype" value="3" type="radio" required @if('3' == old('def_paytype', (isset($supplierData)?($supplierData->def_paytype ?? ''): ''))) checked @endif>
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label" data-type="匯款">
                                <input class="form-check-input" name="def_paytype" value="2" type="radio" required @if('2' == old('def_paytype', (isset($supplierData)?($supplierData->def_paytype ?? ''): ''))) checked @endif>
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label" data-type="支票">
                                <input class="form-check-input" name="def_paytype" value="1" type="radio" required @if('1' == old('def_paytype', (isset($supplierData)?($supplierData->def_paytype ?? ''): ''))) checked @endif>
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label" data-type="其他">
                                <input class="form-check-input" name="def_paytype" value="5" type="radio" required @if('5' == old('def_paytype', (isset($supplierData)?($supplierData->def_paytype ?? ''): ''))) checked @endif>
                            </label>
                        </div>
                    </div>
                </fieldset>
            </div>
            @error('paytype')
            <div class="alert alert-danger mt-3">{{ $message }}</div>
            @enderror
            @error('def_paytype')
            <div class="alert alert-danger mt-3">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-end mt-3">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
        </div>
    </form>
@endsection
@once
    @push('sub-styles')
    <style>
        label.form-check-label[data-type]::after{
            content: attr(data-type)
        }
    </style>
    @endpush
    @push('sub-scripts')
        <script>
            const typeCheckbox = $('label.form-check-label[data-type] input[type="checkbox"]');

            const invoiceShipCheckbox = $('input[name="invoice_ship_fk"]');
            const invoiceShipEmail = $('.invoice_email');

            const invoiceDateCheckbox = $('input[name="invoice_date_fk"]');
            const invoiceDateChecked = $('input[name="invoice_date_fk"]:checked');
            const invoiceDate = $('.invoice_date');
            const invoiceDateOther = $('.invoice_date_other');

            const accountDateCheckbox = $('input[name="account_fk"]');
            const accountDateChecked = $('input[name="account_fk"]:checked');
            const accountDate = $('.account_date');
            const accountDateOther = $('.account_date_other');

            //首次讀取時，隱藏不必要欄位
            //發票寄送日選「月底」
            if (invoiceDateChecked.val() === '1') {
                invoiceDate.hide();
                invoiceDateOther.hide();
            //發票寄送日選「次月幾日前」
            } else if(invoiceDateChecked.val() === '2') {
                invoiceDate.show();
                invoiceDateOther.hide();
            //發票寄送日選「其它」
            } else {
                invoiceDate.hide();
                invoiceDateOther.show();
            }
            //發票寄送日
            invoiceDateCheckbox.on('change', function () {
                //月底前
                if ($(this).val() === '1') {
                    invoiceDate.hide();
                    invoiceDateOther.hide();
                //次月幾天前
                } else if ($(this).val() === '2'){
                    invoiceDate.show();
                    invoiceDateOther.hide();
                //其它
                } else {
                    invoiceDate.hide();
                    invoiceDateOther.show();
                }
            });

            //首次讀取時，隱藏不必要欄位
            //發票寄送：郵寄
            if (invoiceShipCheckbox.val() === '1') {
                invoiceShipEmail.hide();
                //發票寄送：電子檔
            } else if(invoiceShipCheckbox.val() === '2') {
                invoiceShipEmail.show();
            }
            //發票寄送方式
            invoiceShipCheckbox.on('change', function () {
                if ($(this).val() === '1') {
                    invoiceShipEmail.hide();
                    invoiceShipEmail.attr('value', '');
                } else {
                    //顯示電子郵件
                    invoiceShipEmail.show();
                }
            });


            //首次讀取時，隱藏不必要欄位
            // 結帳日選「幾號」
            if (accountDateChecked.val() === '1') {
                accountDate.show();
                accountDateOther.hide();
                //選「月底」
            } else if(accountDateChecked.val() === '2') {
                accountDate.hide();
                accountDateOther.hide();
                //選「其它」
            } else {
                accountDate.hide();
                accountDateOther.show();
            }
            //結帳日
            accountDateCheckbox.on('change', function () {
                //幾號
                if ($(this).val() === '1') {
                    accountDate.show();
                    accountDateOther.hide();
                    //選月底
                } else if ($(this).val() === '2'){
                    accountDate.hide();
                    accountDateOther.hide();
                    //其它
                } else {
                    accountDate.hide();
                    accountDateOther.show();
                }
            });

            typeCheckbox.each(function (index, element) {
                // element == this
                checkType($(element));
            });
            typeCheckbox.on('change', function () {
                checkType($(this));
            });
            function checkType($that) {
                const type = $that.closest('label').attr('data-type');
                if ($that.prop('checked')) {
                    // 付款資訊欄位顯示
                    $that.closest('.form-check').find('.row').show();
                    $that.closest('.form-check').find('.row input').prop({
                        'disabled': false,
                        'required': true
                    });
                    // 預設付款方式
                    $(`div.form-check-inline:has(label[data-type="${type}"])`).show();
                    $(`label.form-check-label[data-type="${type}"] input[type="radio"]`).prop({
                        'disabled': false,
                        'required': true
                    });
                } else {
                    // 付款資訊欄位顯示
                    $that.closest('.form-check').find('.row').hide();
                    $that.closest('.form-check').find('.row input').prop({
                        'disabled': true,
                        'required': false
                    });
                    // 預設付款方式
                    $(`div.form-check-inline:has(label[data-type="${type}"])`).hide();
                    $(`label.form-check-label[data-type="${type}"] input[type="radio"]`).prop({
                        'disabled': true,
                        'required': false,
                        'checked': false
                    });
                }
            }
        </script>
    @endpush
@endonce
