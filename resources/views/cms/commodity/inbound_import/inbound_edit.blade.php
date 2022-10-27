@extends('layouts.main')
@section('sub-content')
<h2 class="mb-3">#{{ $inboundData->sn }} 入庫單庫存調整</h2>

<form method="post" action="{{ $formAction }}" class="-banRedo">
    <div class="card shadow p-4 mb-4">
        @method('POST')
        @csrf
        <dl class="row">
            <div class="col-6 mb-3">
                <dt>採購單號</dt>
                <dd>{{ $inboundData->event_sn ?? '' }}</dd>
            </div>
            <div class="col-6 mb-3">
                <dt>SKU</dt>
                <dd>{{ $inboundData->sku ?? '' }}</dd>
            </div>
            <div class="col-12 mb-3">
                <dt>商品款式名稱</dt>
                <dd>{{ $inboundData->title ?? '' }}</dd>
            </div>
            <div class="col-6 mb-3">
                <dt>入庫單</dt>
                <dd>{{ $inboundData->sn ?? '' }}</dd>
            </div>
            <div class="col-6 mb-3">
                <dt>庫存剩餘數量</dt>
                <dd>{{ $inboundData->remaining_qty ? number_format($inboundData->remaining_qty) : '' }}</dd>
            </div>

            <x-b-form-group name="remaining_qty" title="調整數量" required="false" class="col-12 col-sm-6">
                <input type="number"
                        class="form-control @error('update_num') is-invalid @enderror"
                        name="update_num" value="0" min="{{ $inboundData->remaining_qty ? $inboundData->remaining_qty * -1 : 0 }}"
                        required/>
            </x-b-form-group>
            <x-b-form-group name="remaining_qty" title="調整效期" required="false" class="col-12 col-sm-6">
                <input type="date"
                        class="form-control @error('expiry_date') is-invalid @enderror"
                        name="expiry_date" value="{{ $inboundData->expiry_date ?? '' }}"
                        required/>
            </x-b-form-group>
            <x-b-form-group name="note" title="備註" required="false">
                <input class="form-control @error('memo') is-invalid @enderror"
                        name="memo"
                        type="text"
                        value=""/>
            </x-b-form-group>
            <x-b-form-group name="note" title="審核狀態" required="false">
                <div class="px-1 pt-1">
                    @foreach (App\Enums\Consignment\AuditStatus::asArray() as $key => $val)
                        <div class="form-check form-check-inline @error('inventory_status')is-invalid @enderror">
                            <label class="form-check-label">
                                <input class="form-check-input @error('inventory_status')is-invalid @enderror" name="inventory_status"
                                        value="{{ $val }}" type="radio" required
                                        @if (old('inventory_status', $inboundData->inventory_status ?? App\Enums\Consignment\AuditStatus::unreviewed()->value) == $val) checked @endif>
                                {{ App\Enums\Consignment\AuditStatus::getDescription($val) }}
                            </label>
                        </div>
                    @endforeach
                    @error('inventory_status')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </x-b-form-group>
        </dl>

        <input type='hidden' name='id' value="{{ old('id', $inboundData->id) }}" />
        <input type="hidden" name="backUrl" value="{{ $backUrl }}"/>
    
        @if($errors->any())
            <div class="alert alert-danger mt-3">{!! implode('', $errors->all('<div>:message</div>')) !!}</div>
        @endif
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary px-4">儲存</button>
        <a href="{{ $backUrl }}" class="btn btn-outline-primary" role="button">返回上一頁</a>
    </div>
</form>
@endsection
@once
    @push('sub-styles')
    <style>
        dt {
            font-weight: normal;
            margin-bottom: 0.5rem;
        }
    </style>
    @endpush
@endonce
