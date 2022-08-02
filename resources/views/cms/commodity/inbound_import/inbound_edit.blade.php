@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ URL::previous() }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>
    <div class="card">
        <div class="card-header">
            編輯入庫單
        </div>
        <form class="card-body" method="post" action="{{ $formAction }}">
            @method('POST')
            @csrf
            <x-b-form-group name="purchase_sn" title="採購單號" required="false">
                <div class="col-form-label">{{ $inboundData->event_sn ?? '' }}</div>
            </x-b-form-group>
            <x-b-form-group name="sku" title="SKU" required="false">
                <div class="col-form-label">{{ $inboundData->sku ?? '' }}</div>
            </x-b-form-group>
            <x-b-form-group name="title" title="商品款式名稱" required="false">
                <div class="col-form-label">{{ $inboundData->title ?? '' }}</div>
            </x-b-form-group>
            <x-b-form-group name="inbound_sn" title="入庫單" required="false">
                <div class="col-form-label">{{ $inboundData->sn ?? '' }}</div>
            </x-b-form-group>
            <x-b-form-group name="remaining_qty" title="庫存剩餘數量" required="false">
                <div class="col-form-label">{{ $inboundData->remaining_qty ?? '' }}</div>
            </x-b-form-group>
            <x-b-form-group name="remaining_qty" title="調整數量" required="false">
                <input type="number"
                       class="form-control form-control-sm @error('update_num') is-invalid @enderror"
                       name="update_num" value="0" min="{{ $inboundData->remaining_qty ? $inboundData->remaining_qty * -1 : 0 }}"
                       required/>
            </x-b-form-group>
            <x-b-form-group name="note" title="備註" required="false" class="col-12 col-sm-6 mb-3">
                <input class="form-control @error('memo') is-invalid @enderror"
                       name="memo"
                       type="text"
                       value=""/>
            </x-b-form-group>
            <input type='hidden' name='id' value="{{ old('id', $inboundData->id) }}" />
            <input type="hidden" name="backUrl" value="{{ $backUrl }}"/>
            <div class="d-flex justify-content-end pt-2">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
            </div>
            @if($errors->any())
                <div class="alert alert-danger mt-3">{!! implode('', $errors->all('<div>:message</div>')) !!}</div>
            @endif
        </form>
    </div>
@endsection
@once
    @push('sub-scripts')

    @endpush
@endonce
