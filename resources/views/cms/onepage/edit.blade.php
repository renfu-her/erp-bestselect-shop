@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">
        @if ($method === 'create')
            新增
        @else
            編輯
        @endif 一頁式網站
    </h2>
    <form method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf

        <div class="card shadow p-4 mb-4">
            <div class="row">
                <x-b-form-group name="title" title="名稱" required="true">
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title"
                        name="title" value="{{ old('title', $data->title ?? '') }}" required aria-label="商品群組名稱" />
                </x-b-form-group>
                <x-b-form-group name="country" title="國家" required="true">
                    <input type="text" class="form-control @error('country') is-invalid @enderror" id="country"
                        name="country" value="{{ old('country', $data->country ?? '') }}" required aria-label="國家" />
                </x-b-form-group>

                <x-b-form-group name="sale_channel_id" title="銷售通路" required="true">
                    <select name="sale_channel_id" class="form-select -select2 -single" data-placeholder="請單選">
                        @foreach ($saleChannel as $value)
                            <option value="{{ $value->id }}" @if (old('sale_channel_id', $data->sale_channel_id ?? '') == $value->id) selected @endif>
                                {{ $value->title }}</option>
                        @endforeach
                    </select>
                </x-b-form-group>
                <x-b-form-group name="collection_id" title="商品群組" required="true">
                    <select name="collection_id" class="form-select -select2 -single" data-placeholder="請單選">
                        @foreach ($collection as $value)
                            <option value="{{ $value->id }}" @if (old('collection_id', $data->collection_id ?? '') == $value->id) selected @endif>
                                {{ $value->name }}</option>
                        @endforeach
                    </select>
                </x-b-form-group>
                <x-b-form-group name="view_mode" title="商品檢視模式" required="true">
                    <div class="px-1">
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <i class="bi bi-list"></i> 條列檢視
                                <input class="form-check-input @error('view_mode') is-invalid @enderror" value="1"
                                    name="view_mode" type="radio" @if ('1' == old('view_mode', $data->view_mode ?? '1')) checked @endif>
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <i class="bi bi-grid-3x3-gap-fill"></i> 格狀檢視
                                <input class="form-check-input @error('view_mode') is-invalid @enderror" value="0"
                                    name="view_mode" type="radio" @if ('0' == old('view_mode', $data->view_mode ?? '')) checked @endif>
                            </label>
                        </div>
                    </div>
                </x-b-form-group>

                <x-b-form-group name="online_pay" title="線上付款" required="true">
                    <div class="px-1">
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                有
                                <input class="form-check-input @error('online_pay') is-invalid @enderror" value="1"
                                    name="online_pay" type="radio" @if ('1' == old('online_pay', $data->online_pay ?? '1')) checked @endif>
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                無
                                <input class="form-check-input @error('online_pay') is-invalid @enderror" value="0"
                                    name="online_pay" type="radio" @if ('0' == old('online_pay', $data->online_pay ?? '')) checked @endif>
                            </label>
                        </div>
                    </div>
                </x-b-form-group>
            </div>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
            <a href="{{ Route('cms.onepage.index', [], true) }}" class="btn btn-outline-primary px-4"
                role="button">返回列表</a>
        </div>
    </form>

@endsection
@once
    @push('sub-styles')
        <style>

        </style>
    @endpush
    @push('sub-scripts')
        <script></script>
    @endpush
@endonce
