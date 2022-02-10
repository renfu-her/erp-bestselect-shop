@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.depot.index', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>
    <div class="card">
        <div class="card-header">
            @if ($method === 'create') 新增 @else 編輯 @endif 倉庫
        </div>
        <form class="card-body" method="post" action="{{ $formAction }}">
            @method('POST')
            @csrf
            <x-b-form-group name="name" title="倉庫名稱" required="true">
                <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $data->name ?? '') }}" />
            </x-b-form-group>

            <x-b-form-group name="can_pickup" title="開放自取" required="true">
                <div class="px-1">
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            開放
                            <input class="form-check-input @error('can_pickup') is-invalid @enderror"
                                   value="1"
                                   name="can_pickup"
                                   type="radio"
                                   required
                                   @if ($method === 'edit'
                                        && $data->can_pickup )
                                   checked
                                @endif
                            >
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            關閉
                            <input class="form-check-input @error('can_pickup') is-invalid @enderror"
                                   value="0"
                                   name="can_pickup"
                                   type="radio"
                                   required
                                   @if ($method === 'create' ||
                                        ($method === 'edit' && !$data->can_pickup))
                                   checked
                                @endif
                            >
                        </label>
                    </div>
                </div>
            </x-b-form-group>

            <x-b-form-group name="can_tally" title="理貨倉" required="true">
                <div class="px-1">
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            是
                            <input class="form-check-input @error('can_tally') is-invalid @enderror"
                                   value="1"
                                   name="can_tally"
                                   type="radio"
                                   required
                                   @if ($method === 'edit'
                                        && $data->can_tally )
                                   checked
                                    @endif
                            >
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            否
                            <input class="form-check-input @error('can_tally') is-invalid @enderror"
                                   value="0"
                                   name="can_tally"
                                   type="radio"
                                   required
                                   @if ($method === 'create' ||
                                        ($method === 'edit' && !$data->can_tally))
                                   checked
                                    @endif
                            >
                        </label>
                    </div>
                </div>
            </x-b-form-group>

            <x-b-form-group name="sender" title="倉商窗口" required="true">
                <input class="form-control @error('sender') is-invalid @enderror" name="sender" value="{{ old('sender', $data->sender ?? '') }}" />
            </x-b-form-group>
            <div calss="form-group">
                <label class="col-form-label">
                    地址 <span class="text-danger">*</span>
                </label>
                <div class="input-group has-validation">
                    <select class="form-select @error('city_id') is-invalid @enderror" style="max-width:20%" id="city_id" name="city_id">
                        <option>請選擇</option>
                        @foreach ($citys as $city)
                            <option value="{{ $city['city_id'] }}" @if (old('city_id', $data->city_id ?? '') == $city['city_id']) selected @endif>{{ $city['city_title'] }}</option>
                        @endforeach
                    </select>
                    <select class="form-select @error('region_id') is-invalid @enderror" style="max-width:20%" id="region_id" name="region_id">
                        <option>請選擇</option>
                        @foreach ($regions as $region)
                            <option value="{{ $region['region_id'] }}" @if (old('region_id', $data->region_id ?? '') == $region['region_id']) selected @endif>{{ $region['region_title'] }}</option>
                        @endforeach
                    </select>
                    <input name="addr" type="text" class="form-control @error('addr') is-invalid @enderror"
                        value="{{ old('addr', $data->addr ?? '') }}">
                    <button class="btn btn-outline-success" type="button" id="format_btn">格式化</button>
                    <div class="invalid-feedback">
                        @error('city_id')
                            {{ $message }}
                        @enderror
                        @error('region_id')
                            {{ $message }}
                        @enderror
                        @error('addr')
                            {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>
            <x-b-form-group name="tel" title="電話" required="true">
                <input class="form-control @error('tel') is-invalid @enderror" name="tel" value="{{ old('tel', $data->tel ?? '') }}" />
            </x-b-form-group>

            @if ($method === 'edit')
                <input type='hidden' name='id' value="{{ old('id', $id) }}" />
            @endif
            @error('id')
                <div class="alert alert-danger mt-3">{{ $message }}</div>
            @enderror
            <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
            </div>
        </form>
    </div>

@endsection
@once
    @push('sub-scripts')
        <script>
            let cityElem = $('#city_id');
            let regionElem = $('#region_id')
            let addrInputElem = $('input[name=addr]');

            cityElem.on('change', function(e) {
                getRegionsAction($(this).val());
            });

            function getRegionsAction(city_id, region_id) {
                Addr.getRegions(city_id)
                    .then(re => {
                        Elem.renderSelect(regionElem, re.datas, {
                            default: region_id,
                            key: 'region_id',
                            value: 'region_title'
                        });
                    });
            }

            $('#format_btn').on('click', function(e) {
                let addr = addrInputElem.val();

                if (addr) {
                    Addr.addrFormating(addr).then(re => {
                        addrInputElem.val(re.data.addr);
                        if (re.data.city_id) {
                            cityElem.val(re.data.city_id);
                            getRegionsAction(re.data.city_id, re.data.region_id);

                        }
                    });
                }
            });
        </script>
    @endpush
@endonce
