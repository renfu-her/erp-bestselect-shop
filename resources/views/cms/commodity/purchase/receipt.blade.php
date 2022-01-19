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
                <input type="text" name="" class="form-control" value="系統產生" required>
            </div>
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">支付對象 <span class="text-danger">*</span></label>
                <select class="form-select -select2 -single @error('supplier') is-invalid @enderror"
                        aria-label="支付對象-採購廠商" required>
                    <option value="" selected disabled>請選擇</option>
                    @foreach ($supplierList as $supplierItem)
                        <option value="{{ $supplierItem->id }}"
                                @if ($supplierItem->id == old('supplier', $purchaseData->supplier_id ?? '')) selected @endif>
                            {{ $supplierItem->name }}@if ($supplierItem->nickname)（{{ $supplierItem->nickname }}） @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">類型</label>
                <div class="form-control" readonly>@if ($type === 'deposit')訂金@else 尾款@endif</div>
            </div>
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">商品負責人 <span class="text-danger">*</span></label>
                <select class="form-select -select2 -multiple" multiple name="" data-placeholder="可多選" required>
                    <option value="1" selected>item 1</option>
                    <option value="2">item 2</option>
                    <option value="3" selected>item 3</option>
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
                            <input class="form-check-input" name="type" value="0" type="radio" required>
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <label class="form-check-label" data-type="外幣">
                            <input class="form-check-input" name="type" value="3" type="radio" required>
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <label class="form-check-label" data-type="匯款">
                            <input class="form-check-input" name="type" value="2" type="radio" required>
                        </label>
                        <div class="row" style="display: none;">
                            <x-b-form-group name="bank_cname" title="匯款銀行" required="true" class="col-12 col-sm-6">
                                <input class="form-control @error('bank_cname') is-invalid @enderror" name="bank_cname" value="{{ old('bank_cname', $data->bank_cname ?? '') }}" />
                            </x-b-form-group>
                            <x-b-form-group name="bank_code" title="匯款銀行代碼" required="true" class="col-12 col-sm-6">
                                <input class="form-control @error('bank_code') is-invalid @enderror" name="bank_code" value="{{ old('bank_code', $data->bank_code ?? '') }}" />
                            </x-b-form-group>
                            <x-b-form-group name="bank_acount" title="匯款戶名" required="true" class="col-12 col-sm-6">
                                <input class="form-control @error('bank_acount') is-invalid @enderror" name="bank_acount" value="{{ old('bank_acount', $data->bank_acount ?? '') }}" />
                            </x-b-form-group>
                            <x-b-form-group name="bank_numer" title="匯款帳號" required="true" class="col-12 col-sm-6">
                                <input class="form-control @error('bank_numer') is-invalid @enderror" name="bank_numer" value="{{ old('bank_numer', $data->bank_numer ?? '') }}" />
                            </x-b-form-group>
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <label class="form-check-label" data-type="支票">
                            <input class="form-check-input" name="type" value="1" type="radio" required>
                        </label>
                        <div class="row" style="display: none;">
                            <x-b-form-group name="cheque_payable" title="支票抬頭" required="true" class="col-12">
                                <input class="form-control @error('cheque_payable') is-invalid @enderror" name="cheque_payable" value="{{ old('cheque_payable', $data->cheque_payable ?? '') }}" />
                            </x-b-form-group>
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <label class="form-check-label" data-type="其他">
                            <input class="form-check-input" name="type" value="5" type="radio" required>
                        </label>
                    </div>
                    <div class="invalid-feedback">錯誤訊息</div>
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
            $('input[name="type"]').on('change', function () {
                checkType();
            });
            function checkType() {
                const $checked = $('input[name="type"]:checked');

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
