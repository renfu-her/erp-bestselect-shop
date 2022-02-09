@extends('layouts.main')
@section('sub-content')
    <div>
        <h2 class="mb-3">{{ $product->title }}</h2>
        <x-b-prd-navi :product="$product"></x-b-prd-navi>
    </div>
    <form method="post" action="{{ route('cms.product.edit-setting', ['id' => $product->id]) }}">
        @csrf
        <div class="card shadow p-4 mb-4">
            <h6>設定</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">自訂群組</label>
                    <select name="" class="form-select -select2 -multiple" data-placeholder="請選擇自訂群組" multiple hidden>
                        <option value="1">item 1</option>
                        <option value="2">item 2</option>
                        <option value="3">item 3</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">任選折扣群組</label>
                    <select name="" class="form-select -select2 -multiple" data-placeholder="請選擇任選折扣群組" multiple hidden>
                        <option value="1">item 1</option>
                        <option value="2">item 2</option>
                        <option value="3">item 3</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card shadow p-4 mb-4">
            <h6>物流綁定</h6>
            <div>
                @foreach ($shipments as $key => $value)
                    <div class="col-12 col-sm-6 mb-3">
                        <input type="hidden" name="category_id[]" value={{ $value->id }}>
                        <label class="form-label">{{ $value->category }}</label>
                        <select name="group_id[]" class="form-select">
                            <option value="0">無</option>
                            @foreach ($value->groups as $key2 => $group)
                                <option value="{{ $group->id }}" @if(in_array($group->id,$currentShipment)) selected @endif>{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>
        </div>
        <div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
                <a href="{{ Route('cms.product.index') }}" class="btn btn-outline-primary px-4" role="button">返回列表</a>
            </div>
        </div>
    </form>
@endsection
@once
    @push('sub-styles')
        <style>
        </style>
    @endpush
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endOnce
