@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.shipment.index', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>
    <h2 class="mb-4">
        @if ($method === 'create') 新增 @else 編輯 @endif 物流運費
    </h2>
        <form class="card-body" method="post" action="{{ $formAction }}">
    <div class="card shadow p-4 mb-4">
            @method('POST')
            @csrf
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <x-b-form-group name="name" title="物流運費名稱" required="true">
                        <input class="form-control @error('name') is-invalid @enderror" name="name"
                               value="{{ old('name', $data->name ?? '') }}"/>
                    </x-b-form-group>
                </div>
            </div>
            <div class="row">
                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">運送溫度 <span class="text-danger">*</span></legend>
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" value="常溫" name="temps" type="radio" required >
                            <label class="form-check-label">常溫</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" value="冷藏" name="temps" type="radio">
                            <label class="form-check-label">冷藏</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" value="冷凍" name="temps" type="radio">
                            <label class="form-check-label">冷凍</label>
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class="row">
                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2 ">出貨方式 <span class="text-danger">*</span></legend>
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" name="method" type="radio" required >
                            <label class="form-check-label">喜鴻出貨</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" name="method" type="radio">
                            <label class="form-check-label">廠商出貨</label>
                        </div>
                    </div>
                </fieldset>
            </div>
            <div class="col-12 mb-3">
                <label class="form-label">說明</label>
                <textarea class="form-control" placeholder="" rows="6"></textarea>
            </div>

    </div>

    <div class="card shadow p-4 mb-4">
        <h6>
             物流規則
        </h6>
        <div class="table-responsive tableOverBox">
            <table class="table tableList table-striped">
                <thead>
                <tr>
                    <th scope="col">最少消費金額 <span class="text-danger">*</span></th>
                    <th scope="col"> </th>
                    <th scope="col"> </th>
                    <th scope="col">最多消費金額 <span class="text-danger">*</span></th>
                    <th scope="col">運費 <span class="text-danger">*</span></th>
                    <th scope="col">成本 <span class="text-danger">*</span></th>
                    <th scope="col">最多件數 <span class="text-danger">*</span></th>
                    <th scope="col">刪除 </th>

                </tr>
                </thead>
                <tbody class="-appendClone">
                {{--                                        @if (count($dataList) == 0)--}}
                @if ($method === 'create')
                    <tr>
                        <td>
                            0
{{--                            <input type="text" class="form-control form-control-sm -l" value="0" disabled aria-label=""/>--}}
                        </td>
                        <td>~</td>
                        <td>

                           <select name="is_above_0" class="form-select form-select-sm">
                                <option value="false">未滿</option>
                                <option value="true" >以上</option>
                            </select>

                        </td>
                        <td>
                            <input type="text" name="max_price_0" class="form-control form-control-sm -l" value="" aria-label="" required/>
                        </td>
                        <td>
                            <input type="text" name="dlv_fee_0" class="form-control form-control-sm -l" value="" aria-label="" required/>
                        </td>
                        <td>
                            <input type="text" name="dlv_cost_0" class="form-control form-control-sm -l" value="" aria-label="" required/>
                        </td>
                        <td>
                            <input type="text" name="at_most_0" class="form-control form-control-sm -l" value="" aria-label="" required/>
                        </td>
                        <td></td>
                    </tr>
                @endif
                @if ($method === 'edit')

                    @foreach($dataList as $key => $data)
                        <tr>
                            <td>
                                <input type="text" class="form-control form-control-sm -l" aria-label="" required
                                       @if ($key == 0)
                                       value="0"
                                       disabled
                                       @else
                                       value="{{ $data->min_price }}"
                                       @endif
                                />
                            </td>

{{--                            <td>--}}
{{--                                <input type="text" name="min_price_{{ $key }}" class="form-control form-control-sm -l" value="{{ $data->min_price }}" aria-label=""/>--}}
{{--                            </td>--}}
                            <select name="is_above_{{ $key }}" class="form-select form-select-sm">
                                <option value="false" >未滿</option>
                                <option value="true" @if ($data->is_above == 'true') selected @endif>以上</option>
                            </select>
                            <td>
                                <input name="max_price_{{ $key }}" type="text" class="form-control form-control-sm -l" value="{{ $data->max_price }}" aria-label="" required/>
                            </td>
                            <td>
                                <input type="text" name="dlv_price_{{ $key }}" class="form-control form-control-sm -l" value="{{ $data->dlv_price }}" aria-label="" required/>
                            </td>
                            <td>
                                <input type="text" name="dlv_cost_{{ $key }}" class="form-control form-control-sm -l" value="{{ $data->dlv_cost }}" aria-label="" required/>
                            </td>
                            <td>
                                <input type="text" name="at_most_{{ $key }}" class="form-control form-control-sm -l" value="{{ $data->at_most }}" aria-label="" required/>
                            </td>
                            <td>
                                @if( ($key + 1) == count($dataList))
                            <button type="button"
                            class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                <i class="bi bi-trash"></i>
                            </button>
                                @endif
                            </td>
{{--                            <td>--}}
{{--                                <input type="text" class="form-control form-control-sm -l" value="" aria-label=""/>--}}
{{--                            </td>--}}
{{--                                                <td class="text-center">--}}
{{--                                                    <button type="button"--}}
{{--                                                            class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">--}}
{{--                                                        <i class="bi bi-trash"></i>--}}
{{--                                                    </button>--}}
{{--                                                </td>--}}
                        </tr>
                    @endforeach
                @endif

                </tbody>
            </table>
        </div>
        <div class="mt-3">
            <button type="button" class="btn btn-primary -newClone">新增物流規則</button>
        </div>
    </div>

{{--    @if ($method === 'edit')--}}
{{--        <input type='hidden' name='id' value="{{ old('id', $id) }}"/>--}}
{{--    @endif--}}
{{--    @error('id')--}}
{{--    <div class="alert alert-danger mt-3">{{ $message }}</div>--}}
{{--    @enderror--}}
    <div class="d-flex justify-content-end mt-3">
        <button type="submit" class="btn btn-primary px-4">儲存</button>
    </div>
    </form>
@endsection
@once
    @push('sub-scripts')
        <script>
            let cityElem = $('#city_id');
            let regionElem = $('#region_id')
            let addrInputElem = $('input[name=addr]');

            cityElem.on('change', function (e) {
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

            $('#format_btn').on('click', function (e) {
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
