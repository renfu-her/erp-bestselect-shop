@extends('layouts.main')
@section('sub-content')
    @if ($method === 'edit')
        <h2 class="mb-3">活動名稱</h2>
    @else
        <h2 class="mb-3">新增 現折優惠</h2>
    @endif

    <form id="form1" method="post" action="">
        @method('POST')
        @csrf

        <div class="card shadow p-4 mb-4">
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">活動名稱 <span class="text-danger">*</span></label>
                    <input class="form-control" name="title" type="text" placeholder="請輸入活動名稱" required aria-label="活動名稱">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">消費金額 <span class="text-danger">*</span></label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                        <input type="number" class="form-control" name="" min="0" value="" placeholder="請輸入消費金額" required>
                    </div>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">活動開始時間</label>
                    <div class="input-group has-validation">
                        <input type="datetime-local" name="start_date" value=""
                                class="form-control" aria-label="活動開始時間"/>
                        <button class="btn btn-outline-secondary icon" type="button" data-clear
                                data-bs-toggle="tooltip" title="清空時間"><i class="bi bi-calendar-x"></i>
                        </button>
                        <div class="invalid-feedback">
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">活動結束時間</label>
                    <div class="input-group has-validation">
                        <input type="datetime-local" name="end_date" value=""
                                class="form-control" aria-label="活動結束時間"/>
                        <button class="btn btn-outline-secondary icon" type="button" data-clear
                                data-bs-toggle="tooltip" title="清空時間"><i class="bi bi-calendar-x"></i>
                        </button>
                        <div class="invalid-feedback">
                        </div>
                    </div>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">適用商品群組（多選）</label>
                    <select name="select[]" multiple class="-select2 -multiple form-select" data-close-on-select="false" data-placeholder="不選為全館適用">
                        <option value="1">item 1</option>
                        <option value="2">item 2</option>
                        <option value="3">item 3</option>
                    </select>
                </div>
                <fieldset class="col-12 mb-1">
                    <legend class="col-form-label p-0 mb-2">優惠方式 <span class="text-danger">*</span></legend>
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="method_code" type="radio" value="cash" required>
                                金額
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="method_code" type="radio" value="percent" required>
                                百分比
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="method_code" type="radio" value="coupon" required>
                                優惠劵
                            </label>
                        </div>
                    </div>
                </fieldset>

                {{-- 優惠方式：金額 cash --}}
                <div class="row mb-3 border rounded mx-0 px-0 pt-2" data-method="cash" hidden>
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">折扣金額 <span class="text-danger">*</span></label>
                        <div class="input-group flex-nowrap">
                            <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                            <input type="number" name="" class="form-control" min="0" value="" placeholder="請輸入折扣金額">
                        </div>
                    </div>
                    <fieldset class="col-12 col-sm-6 mb-3">
                        <legend class="col-form-label p-0 mb-2">&nbsp;</legend>
                        <div class="px-1 pt-1">
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="" type="checkbox" value="1" checked norequired>
                                    累計折扣
                                </label>
                            </div>
                        </div>
                    </fieldset>
                </div>

                {{-- 優惠方式：百分比 percent --}}
                <div class="row mb-3 border rounded mx-0 px-0 pt-2" data-method="percent" hidden>
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">折扣百分比 <span class="text-danger">*</span></label>
                        <div class="input-group flex-nowrap">
                            <input type="number" name="" class="form-control" min="1" max="100" value="" placeholder="請輸入百分比 1 ~ 100">
                            <span class="input-group-text"><i class="bi bi-percent"></i></span>
                        </div>
                    </div>
                </div>

                {{-- 優惠方式：優惠劵 coupon --}}
                <div class="row mb-3 border rounded mx-0 px-0 pt-2" data-method="coupon" hidden>
                    <div class="col-12 mb-3">
                        <label class="form-label">指定贈送優惠券 <span class="text-danger">*</span></label>
                        <select class="form-select -select2 -single" aria-label="指定贈送優惠券">
                            <option value="" selected disabled>請選擇</option>
                            <option value="1">item 1</option>
                            <option value="2">item 2</option>
                            <option value="3">item 3</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div id="submitDiv">
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
                <a href="{{ Route('cms.discount.index') }}" class="btn btn-outline-primary px-4"
                   role="button">返回列表</a>
            </div>
        </div>
    </form>

    {{-- 群組清單 --}}
    <x-b-modal id="addProduct" cancelBtn="false" size="modal-xl modal-fullscreen-lg-down">
        <x-slot name="title">選取商品加入採購清單</x-slot>
        <x-slot name="body">
            <div class="input-group mb-3 -searchBar">
                <input type="text" class="form-control" placeholder="請輸入名稱或SKU" aria-label="搜尋條件">
                <button class="btn btn-primary" type="button">搜尋商品</button>
            </div>
            {{-- <div class="row justify-content-end mb-2">
                <div class="col-auto">
                    顯示
                    <select class="form-select d-inline-block w-auto" id="dataPerPageElem" aria-label="表格顯示筆數">
                        @foreach (config('global.dataPerPage') as $value)
                            <option value="{{ $value }}">{{ $value }}</option>
                        @endforeach
                    </select>
                    筆
                </div>
            </div> --}}
            <div class="table-responsive">
                <table class="table table-hover tableList">
                    <thead>
                    <tr>
                        <th scope="col" class="text-center">選取</th>
                        <th scope="col">商品名稱</th>
                        <th scope="col">款式</th>
                        <th scope="col">SKU</th>
                        <th scope="col">庫存數量</th>
                        <th scope="col">預扣庫存量</th>
                    </tr>
                    </thead>
                    <tbody class="-appendClone --product">
                    <tr>
                        <th class="text-center">
                            <input class="form-check-input" type="checkbox"
                                   value="" data-td="p_id" aria-label="選取商品">
                        </th>
                        <td data-td="name">【喜鴻嚴選】咖啡候機室(10入/盒)</td>
                        <td data-td="spec">綜合口味</td>
                        <td data-td="sku">AA2590</td>
                        <td>58</td>
                        <td>20</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="col d-flex justify-content-end align-items-center flex-wrap -pages"></div>
            <div class="alert alert-secondary mx-3 mb-0 -emptyData" style="display: none;" role="alert">
                查無資料！
            </div>
        </x-slot>
        <x-slot name="foot">
            <span class="me-3 -checkedNum">已選取 0 件商品</span>
            <button type="button" class="btn btn-primary btn-ok">加入採購清單</button>
        </x-slot>
    </x-b-modal>
@endsection
@once
    @push('sub-scripts')
        <script>
            $('input[name="method_code"]').on('change', function () {
                const method = $(this).val();

                // hidden
                $(`div[data-method]:not([data-method="${method}"])`).prop('hidden', true);
                $(`div[data-method]:not([data-method="${method}"])`).find('input, select').prop('required', false);

                // shown
                $(`div[data-method="${method}"]`).prop('hidden', false);
                $(`div[data-method="${method}"]`).find('input:not([norequired]), select:not([norequired])').prop('required', true);
            });
        </script>
    @endpush
@endonce

