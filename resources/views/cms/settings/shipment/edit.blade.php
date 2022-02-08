@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.shipment.index', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>

    <form method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf

        <div class="card mb-4">
            <div class="card-header">@if ($method === 'create') 新增 @else 編輯 @endif 物流運費</div>
            <div class="card-body">
                <div class="row">
                    <x-b-form-group name="name" title="物流名稱" required="true">
                        <input class="form-control @error('name') is-invalid @enderror"
                            name="name" placeholder="請輸入物流名稱"
                            value="{{ old('shipName', $shipName ?? '') }}"/>
                    </x-b-form-group>
                    <x-b-form-group name="temps" title="溫層" required="true">
                        <div class="px-1">
                            @foreach($shipTemps as $key => $temps_data)
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input @error('temps') is-invalid @enderror"
                                            value="{{ old('temps', $temps_data->temps ?? '')}}"
                                            name="temps"
                                            type="radio"
                                            required
                                            readonly
                                            @if ($method == 'edit' &&
                                                old('temps', $dataList[0]->temps ?? '') == $temps_data->temps)
                                                checked
                                            @endif
                                        > {{ $temps_data->temps }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </x-b-form-group>
                    <x-b-form-group name="method" title="出貨方式" required="true">
                        <div class="px-1">
                            @foreach($shipMethods as $key => $shipMethod)
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input @error('method') is-invalid @enderror"
                                            name="method"
                                            value="{{ $shipMethod->method }}"
                                            type="radio"
                                            required
                                            @if ($method == 'edit' &&
                                                old('method', $dataList[0]->method ?? '') == $shipMethod->method)
                                                checked
                                                @endif
                                        > {{ $shipMethod->method }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </x-b-form-group>
                    <x-b-form-group name="note" title="說明" required="true">
                        <textarea name="note" class="form-control" placeholder="請輸入物流說明"
                            rows="6">{{ old('note', $note ?? '') }}</textarea>
                    </x-b-form-group>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">物流規則</div>
            <div class="card-body px-4">
                <div class="table-responsive tableOverBox">
                    <table class="table tableList table-striped">
                        <thead>
                        <tr>
                            <th scope="col">刪除</th>
                            <th scope="col">最少消費金額 <span class="text-danger">*</span></th>
                            <th scope="col"></th>
                            <th scope="col">以上/未滿 <span class="text-danger">*</span></th>
                            <th scope="col">最多消費金額 <span class="text-danger">*</span></th>
                            <th scope="col">運費 <span class="text-danger">*</span></th>
                            <th scope="col">成本</th>
                            <th scope="col">最多件數</th>
                        </tr>
                        </thead>
                        <tbody class="-appendClone">
                        @if ($method === 'create')
                            <tr>
                                <td></td>
                                <td>
                                    <input name="min_price[]"
                                        type="number"
                                        class="form-control form-control-sm -l @error('min_price.*') is-invalid @enderror"
                                        value="0"
                                        readonly
                                        aria-label=""/>
                                </td>
                                <td>~</td>
                                <td>
                                    <select name="is_above[]"
                                            class="form-select form-select-sm @error('is_above.*') is-invalid @enderror">
                                        <option value="false">未滿</option>
                                        <option value="true">以上</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number"
                                        name="max_price[]"
                                        class="form-control form-control-sm -l @error('max_price.*') is-invalid @enderror"
                                        value=""
                                        aria-label=""
                                        required/>
                                </td>
                                <td>
                                    <input type="number"
                                        name="dlv_fee[]"
                                        class="form-control form-control-sm -l @error('dlv_fee.*') is-invalid @enderror"
                                        value=""
                                        aria-label=""
                                        required/>
                                </td>
                                <td>
                                    <input type="number"
                                        name="dlv_cost[]"
                                        class="form-control form-control-sm -l @error('dlv_cost.*') is-invalid @enderror"
                                        value=""
                                        aria-label=""/>
                                </td>
                                <td>
                                    <input type="number"
                                        name="at_most[]"
                                        class="form-control form-control-sm -l @error('at_most.*') is-invalid @enderror"
                                        value=""
                                        aria-label=""/>
                                </td>
                            </tr>
                        @endif
                        @if ($method === 'edit')
                            @foreach($dataList as $key => $data)
                                <tr>
                                    <td>
                                        @if($key == array_key_last($dataList->toArray()) &&
                                            $key != 0)
                                            <button type="button"
                                                    class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </td>
                                    <td>
                                        <input name="min_price[]"
                                            type="number"
                                            class="form-control form-control-sm -l @error('min_price.*') is-invalid @enderror"
                                            aria-label=""
                                            required
                                            value="{{ $data->min_price }}"
                                            @if ($key == 0)
                                                readonly
                                                @endif
                                        />
                                    </td>
                                    <td>~</td>
                                    <td>
                                        <select name="is_above[]" class="form-select form-select-sm @error('is_above.*') is-invalid @enderror">
                                            <option value="false">未滿</option>
                                            <option value="true" @if ($data->is_above == 'true') selected @endif>以上</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input name="max_price[]"
                                            type=
                                                    @if ($data->is_above == 'true')
                                                        "hidden"
                                                    @else
                                                        "number"
                                                    @endif
                                                class="form-control form-control-sm -l @error('max_price.*') is-invalid @enderror"
                                                value="{{ $data->max_price }}"
                                                aria-label="" required/>
                                    </td>
                                    <td>
                                        <input type="number"
                                            name="dlv_fee[]"
                                            class="form-control form-control-sm -l @error('dlv_fee.*') is-invalid @enderror"
                                            value="{{ $data->dlv_fee }}"
                                            aria-label=""
                                            required/>
                                    </td>
                                    <td>
                                        <input type="number"
                                            name="dlv_cost[]"
                                            id="dlv_cost[]"
                                            class="form-control form-control-sm -l @error('dlv_cost.*') is-invalid @enderror"
                                            value="{{ $data->dlv_cost }}"
                                            aria-label=""/>
                                    </td>
                                    <td>
                                        <input type="number"
                                            name="at_most[]"
                                            class="form-control form-control-sm -l @error('at_most.*') is-invalid @enderror"
                                            value="{{ $data->at_most }}"
                                            aria-label=""/>
                                    </td>
                                </tr>
                            @endforeach
                        @endif

                        </tbody>
                    </table>
                </div>
                <div class="d-grid mt-3">
                    <button type="button"
                            class="btn btn-outline-primary border-dashed add_ship_rule"
                            style="font-weight: 500;">
                        <i class="bi bi-plus-circle bold"></i> 新增物流規則
                    </button>
                </div>
            </div>
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

        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
            <a href="{{ Route('cms.shipment.index') }}" class="btn btn-outline-primary px-4"
                role="button">返回列表</a>
        </div>
    </form>
@endsection
@once
    @push('sub-scripts')
        <script>
            let addNewShipElem = $('.add_ship_rule');
            let submitElem = $('form[method=post]');
            const trashButtonHtml = '<button type="button" class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0"> ' +
                                        '<i class="bi bi-trash"></i> ' +
                                    '</button>';

            addNewShipElem.on('click', function () {
                if ($('tbody > tr').length === 1) {
                    // $('tbody > tr:last-child > td:last-child').append(trashHtml);
                    $(trashButtonHtml).appendTo('tbody >tr:last-child > td:first-child');
                }
                let $newShipRuleElem = $('tbody > tr:last-child').clone();

                $('tbody > tr:last-child > td:first-child button').remove();

                let lastShipRuleElem = $('tbody > tr:last-child');
                let lastMaxPrice = $('input[name="max_price[]"]', lastShipRuleElem).val();
                $('input[name="min_price[]"]', $newShipRuleElem).val(lastMaxPrice);
                $('select[name="is_above[]"]', $newShipRuleElem).val('true');
                $('input[name="max_price[]"]', $newShipRuleElem).attr('type', 'hidden');
                $('input[name="max_price[]"]', $newShipRuleElem).attr('value', lastMaxPrice);
                // $('input[name="max_price[]"]', $newShipRuleElem).prop('readonly', true);
                $('input[name="dlv_fee[]"]', $newShipRuleElem).val('');
                $('input[name="dlv_cost[]"]', $newShipRuleElem).val('');
                $('input[name="at_most[]"]', $newShipRuleElem).val('');
                $newShipRuleElem.appendTo('tbody');
            })

            //delete table row
            $('tbody').on('click', 'td:first-child button', function () {
                let $newTrashElem = $('tr:last-child td:first-child button').clone();
                $('tbody > tr:last-child').remove();
                if ($('tbody > tr').length > 1) {
                    $newTrashElem.appendTo('tbody > tr:last-child > td:first-child');
                }
            })

            // hide/show 最多消費金額 by changing 以上/未滿
            $('tbody').on('change', 'select[name="is_above[]"]', function () {
                if($(this).val() === 'true') {
                    let minPrice = $(this).parent().prev().prev().find('input[name="min_price[]"]').val();
                    $(this).parent().next().find('input[name="max_price[]"]').attr('type', 'hidden');
                    $(this).parent().next().find('input[name="max_price[]"]').val(minPrice);
                } else if($(this).val() === 'false') {
                    $(this).parent().next().find('input[name="max_price[]"]').attr('type', 'number');
                }
            })

            // validation before submitting form
            // every validation could be found in each toast.show message
            submitElem.submit(function (event) {
                var allMinMaxPrice = [];

                //「最多消費金額」不能少於「最少消費金額」
                $('tbody > tr').each(function (index, item) {
                    let minPriceElem = $(item).find('input[name="min_price[]"]');
                    let maxPriceElem = $(item).find('input[name="max_price[]"]');

                    //set to default background
                    minPriceElem.removeClass('bg-danger');
                    maxPriceElem.removeClass('bg-danger');

                    if (minPriceElem.val() > maxPriceElem.val()) {
                        toast.show('「最多消費金額」不能少於「最少消費金額」', {title: '錯誤訊息', type: 'danger'});
                        minPriceElem.addClass('bg-danger');
                        maxPriceElem.addClass('bg-danger');
                        event.preventDefault();
                    }

                    allMinMaxPrice.push({
                        min: parseInt(minPriceElem.val()),
                        max: parseInt(maxPriceElem.val())
                    })
                })

                if (allMinMaxPrice[0]['min'] !== 0) {
                    console.log(allMinMaxPrice);
                    toast.show('「最少消費金額」的初始值不是0元', {title: '錯誤訊息', type: 'danger'});
                    event.preventDefault();
                }
                if (allMinMaxPrice.length === 1 &&
                    allMinMaxPrice[0]['max'] !== 0) {
                    toast.show('「最高消費金額」的初始值不是0元', {title: '錯誤訊息', type: 'danger'});
                    event.preventDefault()
                }

                for (let rowIndex = 0; rowIndex < allMinMaxPrice.length; rowIndex++) {
                    if (allMinMaxPrice[rowIndex]['min'] < 0 ||
                        allMinMaxPrice[rowIndex]['max'] < 0) {
                        toast.show('消費金額不能少於0', {title: '錯誤訊息', type: 'danger'});
                        event.preventDefault();
                    }
                }

                for (let rowIndex = 1; rowIndex < allMinMaxPrice.length; rowIndex++) {
                    if (allMinMaxPrice[rowIndex - 1]['max'] !==
                        allMinMaxPrice[rowIndex]['min']) {
                        toast.show('消費金額規則沒有涵蓋所有範圍，第' + rowIndex  + '列的「最多消費金額」要跟' +
                                                            '第' + (rowIndex + 1) + '列的「最少消費金額」相同',
                            {title: '錯誤訊息', type: 'danger'});
                        event.preventDefault();
                    }
                }

                //消費金額的規則最後一組為「以上」，其它都是「未滿」
                $('tbody > tr').each(function (index, item) {
                    let allIsAboveElem = $('tbody > tr select[name="is_above[]"]')
                    let isAboveElem = $(item).find('select[name="is_above[]"]');

                    //set to default background
                    isAboveElem.removeClass('bg-danger');

                    if (allIsAboveElem.length === (index + 1)) {
                        if (isAboveElem.val() !== 'true') {
                            toast.show('消費金額規則沒有涵蓋所有範圍，最後1列沒有選擇「以上」', {title: '錯誤訊息', type: 'danger'});
                            isAboveElem.addClass('bg-danger');
                            event.preventDefault();
                        }
                    } else {
                        if (isAboveElem.val() !== 'false') {
                            toast.show('消費金額規則沒有涵蓋所有範圍，第' + (index + 1) + '列沒有選擇「未滿」', {title: '錯誤訊息', type: 'danger'});
                            isAboveElem.addClass('bg-danger');
                            event.preventDefault();
                        }
                    }
                })

                //運費、成本、最多件數不能小於0
                $('tbody > tr').each(function (index, item) {
                    let dlvFee  = $(item).find('input[name="dlv_fee[]"]');
                    let dlvCost  = $(item).find('input[name="dlv_cost[]"]');
                    let atMost  = $(item).find('input[name="at_most[]"]');

                    //set to default background
                    dlvFee.removeClass('bg-danger');
                    dlvCost.removeClass('bg-danger');
                    atMost.removeClass('bg-danger');

                    if (parseInt(dlvFee.val()) < 0) {
                        toast.show('運費不能少於0', {title: '錯誤訊息', type: 'danger'});
                        dlvFee.addClass('bg-danger');
                        event.preventDefault();
                    }
                    if (parseInt(dlvCost.val()) < 0) {
                        toast.show('成本不能少於0', {title: '錯誤訊息', type: 'danger'});
                        dlvCost.addClass('bg-danger');
                        event.preventDefault();
                    }
                    if (parseInt(atMost.val()) < 0) {
                        toast.show('最多件數不能少於0', {title: '錯誤訊息', type: 'danger'});
                        atMost.addClass('bg-danger');
                        event.preventDefault();
                    }
                })
            })
        </script>
    @endpush
@endonce
