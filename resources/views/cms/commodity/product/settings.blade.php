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
            </div>
        </div>
        <div class="card shadow p-4 mb-4">
            <h6>物流綁定</h6>
            <div>
                @foreach ($shipments as $key => $value)
                    @if ($value->category === '全家')
                        <div class="col-12 col-sm-6 mb-3">
                            {{-- <input type="hidden" name="category_id[]" value={{ $value->id }}> --}}
                            <label class="form-label">全家(待串接開發)</label>
                            <select name="group_id[]" class="form-select" disabled>
                                <option value="0">無</option>
                                @foreach ($value->groupConcat as $key2 => $group)
                                    <option value="{{ $group->id }}" @if (in_array($group->id, $currentShipment)) selected @endif>
                                        {{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @elseif($value->category === '自取')
                    @else
                        <div class="col-12 col-sm-6 mb-3">
                            <input type="hidden" name="category_id[]" value={{ $value->id }}>
                            <label class="form-label">{{ $value->category }}</label>
                            <select name="group_id[]" class="form-select">
                                <option value="0">無</option>
                                @foreach ($value->groupConcat as $key2 => $group)
                                    <option value="{{ $group->id }}" @if (in_array($group->id, $currentShipment)) selected @endif>
                                        {{ $group->name }}</option>
                                @endforeach
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
