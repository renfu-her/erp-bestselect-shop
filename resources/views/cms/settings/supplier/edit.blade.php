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
                    <x-b-form-group name="nickname" title="廠商簡稱" required="false" class="col-12 col-sm-6">
                        <input class="form-control @error('nickname') is-invalid @enderror" name="nickname" value="{{ old('nickname', (isset($supplierData)?($supplierData->nickname ?? ''): '')) }}" />
                    </x-b-form-group>
                    <x-b-form-group name="vat_no" title="統編" required="true" class="col-12 col-sm-6">
                        <input class="form-control @error('vat_no') is-invalid @enderror" name="vat_no" value="{{ old('vat_no', (isset($supplierData)?($supplierData->vat_no ?? ''): '')) }}" />
                    </x-b-form-group>
                    <x-b-form-group name="contact_person" title="廠商窗口" required="true" class="col-12 col-sm-6">
                        <input class="form-control @error('contact_person') is-invalid @enderror" name="contact_person" value="{{ old('contact_person', (isset($supplierData)?($supplierData->contact_person ?? ''): '')) }}" />
                    </x-b-form-group>
                    <x-b-form-group name="contact_tel" title="廠商聯絡電話" required="true" class="col-12 col-sm-6">
                        <input class="form-control @error('contact_tel') is-invalid @enderror" name="contact_tel" value="{{ old('contact_tel', (isset($supplierData)?($supplierData->contact_tel ?? ''): '')) }}" />
                    </x-b-form-group>
                    <x-b-form-group name="email" title="電子郵件" required="false" class="col-12 col-sm-6">
                        <input class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', (isset($supplierData)?($supplierData->email ?? ''): '')) }}" />
                    </x-b-form-group>
                    <x-b-form-group name="contact_address" title="廠商地址" required="true">
                        <input class="form-control @error('contact_address') is-invalid @enderror" name="contact_address" value="{{ old('contact_address', (isset($supplierData)?($supplierData->contact_address ?? ''): '')) }}" />
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
