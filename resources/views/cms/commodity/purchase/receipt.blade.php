@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">採購單 {{ $purchaseData->purchase_sn }}</h2>
    <x-b-pch-navi :id="$id"></x-b-pch-navi>

    <div class="card shadow p-4 mb-4">
        <h6>付款資訊</h6>
        <div class="row">
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">單據</label>
                <div class="form-control" readonly>
                    <a href="{{ Route('cms.purchase.edit', ['id' => $id], true) }}">{{ $purchaseData->purchase_sn }}</a>
                </div>
            </div>
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">付款單號 <span class="text-danger">*</span></label>
                <input type="text" name="" class="form-control" value="系統產生" required readonly>
            </div>
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">支付對象 <span class="text-danger">*</span></label>

                <input type="hidden" name="supplier_id" class="form-control" value="{{$purchaseData->supplier_id}}" >
                <input type="text" name="supplier_name" class="form-control" value="{{$purchaseData->supplier_name}} @if ($purchaseData->supplier_nickname)（{{ $purchaseData->supplier_nickname }}） @endif" readonly>
            </div>
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">類型</label>
                <div class="form-control" readonly>@if ($type === 'deposit')訂金@else 尾款@endif</div>
            </div>
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">商品負責人 <span class="text-danger">*</span></label>
                <select class="form-select -select2 -multiple" multiple name="chargeman" data-placeholder="可多選" required disabled>
                    @foreach ($purchaseChargemanList as $chargemanItem)
                        <option value="{{ $chargemanItem->user_id }}"
                                @if ($chargemanItem->user_id == old('chargeman', $chargemanItem->user_id ?? '')) selected @endif>
                            {{ $chargemanItem->user_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="card shadow p-4 mb-4">
        <h6>付款方式</h6>
        <div class="row">
            <fieldset class="col-12 mb-3 -payType">
                <legend class="col-form-label p-0 mb-2">付款方式 <span class="text-danger">*</span></legend>
                <div class="px-1 pt-1">
                    <div class="form-check mb-3">
                        <label class="form-check-label" data-type="現金">
                            <input class="form-check-input" name="paytype" value="0" type="radio" @if('0' == old('paytype.0', $supplier->def_paytype ?? '')) checked @endif>
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <label class="form-check-label" data-type="外幣">
                            <input class="form-check-input" name="paytype" value="3" type="radio" @if('0' == old('paytype.1', $supplier->def_paytype ?? '')) checked @endif>
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <label class="form-check-label" data-type="匯款">
                            <input class="form-check-input" name="paytype" value="2" type="radio" @if('2' == old('paytype.2', $supplier->def_paytype ?? '')) checked @endif>
                        </label>
                        <div class="row" style="display: none;">
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
                            <input class="form-check-input" name="paytype" value="1" type="radio" @if('1' == old('paytype.3', $supplier->def_paytype ?? '')) checked @endif>
                        </label>
                        <div class="row" style="display: none;">
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
                            <input class="form-check-input" name="paytype" value="5" type="radio" @if('5' == old('paytype.4', $supplier->def_paytype ?? '')) checked @endif>
                        </label>
                    </div>
                    @error('paytype')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </fieldset>
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">期望付款日期 <span class="text-danger">*</span></label>
                <input type="date" name="" class="form-control">
            </div>
        </div>
    </div>

    <div class="card shadow p-4 mb-4">
    @if ($type === 'deposit')
        <h6>訂金付款項目</h6>
        <div class="row">
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">摘要 <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="" value="訂金" placeholder="訂金">
            </div>
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">金額 <span class="text-danger">*</span></label>
                <div class="input-group has-validation">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control" value="" min="1" required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
    @else
        <h6>尾款付款項目</h6>
        <div class="row">
            <div class="col-12 mb-3">
                <label class="form-label">摘要 <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="" placeholder="商品名稱款式/數量/單價/應付金額/採購備註">
            </div>
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">合計 <span class="text-danger">*</span></label>
                <div class="input-group has-validation">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control" value="" min="1" required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
    @endif
    </div>

    <div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
            <a href="{{ Route('cms.purchase.edit', ['id' => $id], true) }}" class="btn btn-outline-primary px-4"
                role="button">取消</a>
        </div>
    </div>

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
            checkType();
            $('input[name="paytype"]').on('change', function () {
                checkType();
            });
            function checkType() {
                const $checked = $('input[name="paytype"]:checked');

                $('fieldset.-payType .form-check > .row').hide();
                $('fieldset.-payType .form-check > .row input').prop({
                    'disabled': true,
                    'required': false
                });
                // 付款資訊欄位顯示
                $checked.closest('.form-check').find('.row').show();
                $checked.closest('.form-check').find('.row input').prop({
                    'disabled': false,
                    'required': true
                });

            }
        </script>
    @endpush
@endonce
