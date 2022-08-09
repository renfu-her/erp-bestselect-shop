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
                    <select name="collection[]" class="form-select -select2 -multiple" data-placeholder="請選擇自訂群組" multiple
                        hidden>
                        @foreach ($collections as $collection)
                            <option value="{{ $collection->id }}" @if (in_array($collection->id, $collection_ids)) selected @endif>
                                {{ $collection->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">店長推薦</label>
                    <select name="recommend_collection_id" class="form-select -select2" data-placeholder="請選擇店長推薦群組"
                            hidden>
                        <option value="" selected disabled>請選擇</option>
                        @foreach ($collections as $collection)
                            <option value="{{ $collection->id }}" @if ($collection->id == $product->recommend_collection_id ?? null) selected @endif>
                                {{ $collection->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">同賣場商品範圍</label>
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="only_show_category" type="radio" value="0" @if ('0' == $product->only_show_category) checked @endif>
                                不限商品歸類
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="only_show_category" type="radio" value="1" @if ('1' == $product->only_show_category) checked @endif>
                                僅提供同類商品
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow p-4 mb-4">
            <h6>物流綁定</h6>
            <div class="row">
                @foreach ($shipments as $key => $value)
                    @if ($value->category === '全家')
                        <div class="col-12 col-sm-6 mb-3">
                            {{-- <input type="hidden" name="category_id[]" value={{ $value->id }}> --}}
                            <label class="form-label">全家(待串接開發)</label>
                            <select name="group_id[]" class="form-select" disabled>
                                <option value="0">無</option>
                                @if(isset($value->groupConcat))
                                @foreach ($value->groupConcat as $key2 => $group)
                                    <option value="{{ $group->id }}" @if (in_array($group->id, $currentShipment)) selected @endif>
                                        {{ $group->name }}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                    @elseif($value->category === '自取')
                    @else
                        <div class="col-12 col-sm-6 mb-3">
                            <input type="hidden" name="category_id[]" value={{ $value->id }}>
                            <label class="form-label">{{ $value->category }}</label>
                            <select name="group_id[]" class="form-select">
                                <option value="0">無</option>
                                @if(isset($value->groupConcat))
                                @foreach ($value->groupConcat as $key2 => $group)
                                    <option value="{{ $group->id }}" @if (in_array($group->id, $currentShipment)) selected @endif>
                                        {{ $group->name }}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                    @endif
                @endforeach

                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">自取</label>
                    <select name="depot_id[]" class="form-select -select2 -multiple" data-placeholder="請選擇門市（可複選）" multiple
                        hidden>
                        @foreach ($allPickup as $key => $depot)
                            <option value="{{ $depot->id }}" @if (in_array($depot->id, $currentPickup)) selected @endif>
                                {{ $depot->name }}</option>
                        @endforeach
                    </select>
                </div>
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
