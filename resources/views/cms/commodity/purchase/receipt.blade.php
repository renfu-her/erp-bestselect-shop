@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">採購單 {{ $purchaseData->purchase_sn }}</h2>
    <x-b-pch-navi :id="$id"></x-b-pch-navi>

    <form action="{{ $formAction }}" method="POST">
    @csrf
    <div class="card shadow p-4 mb-4">
    @if ($type === 'deposit')
        <h6>訂金付款項目</h6>
        <div class="row">
            <input type="hidden" name="type" value="0">
            <div class="col-12 mb-3">
                <label class="form-label">摘要 <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="summary" value="訂金" placeholder="訂金">
            </div>
            <div class="col-12 mb-3">
                <label class="form-label">金額 <span class="text-danger">*</span></label>
                <div class="input-group has-validation">
                    <span class="input-group-text">$</span>
                    <input type="number" name="price" class="form-control" value="" min="1" required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-12 mb-3">
                <label class="form-label">備註 </label>
                <input type="text" class="form-control" name="memo" value="" placeholder="備註">
            </div>
        </div>
    @else
        <h6>尾款付款項目</h6>
        <div class="row">
            <input type="hidden" name="type" value="1">
            <div class="col-12 mb-3">
                <label class="form-label">摘要 <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="summary" value="尾款" placeholder="尾款">
            </div>
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">合計 <span class="text-danger">*</span></label>
                <div class="input-group has-validation">
                    <span class="input-group-text">$</span>
                    <input type="number" name="price" class="form-control" value="" min="1" required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-12 mb-3">
                <label class="form-label">備註 </label>
                <input type="text" class="form-control" name="memo" value="" placeholder="備註">
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
