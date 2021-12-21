@extends('layouts.main')
@section('sub-content')
    <div>
        <h2 class="mb-3">{{ $data->title }}</h2>
        <x-b-prd-navi id="{{ $data->id }}"></x-b-prd-navi>
    </div>

    <div class="card shadow p-4 mb-4">
        <h6>規格管理</h6>
        <div class="table-responsive tableOverBox">
            <table id="spec_table" class="table tableList table-striped">
                <thead>
                    <tr>
                        <th scope="col"></th>
                        <th scope="col">規格</th>
                        <th scope="col">項目</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($specList as $key => $spec)
                        <tr>
                            <th scope="row">規格{{ $key + 1 }}</th>
                            <td>{{ $spec->title }}</td>
                            <td>
                                {{ $spec->item }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div>
            <a href="{{ Route('cms.product.edit-spec', ['id' => $data->id]) }}" class="btn btn-primary px-4">編輯規格</a>
        </div>
    </div>

    <form action="">
        <div class="card shadow p-4 mb-4">
            <h6>款式管理</h6>
            <p>尚無款式，請先至規格管理新增規格</p>
            <div class="table-responsive tableOverBox">
                <table class="table tableList table-striped">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center">上架</th>
                            <th scope="col">SKU <button type="button" class="btn btn-primary btn-sm">產生SKU碼</button></th>
                            @foreach ($specList as $key => $spec)
                                <th scope="col">{{ $spec->title }}</th>
                            @endforeach
                            <th scope="col">成本</th>
                            <th scope="col">庫存</th>
                            <th scope="col">安全庫存</th>
                            <th scope="col">庫存不足</th>
                            <th scope="col" class="text-center">刪除</th>
                        </tr>
                    </thead>
                    <tbody class="-appendClone">
                        <tr class="-cloneElem">
                            <td class="text-center">
                                <div class="form-check form-switch form-switch-lg">
                                    <input class="form-check-input" type="checkbox">
                                </div>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm -l" value="P211105001-42"
                                    aria-label="訂單編號" readonly />
                            </td>

                            @foreach ($specList as $spec)
                                <td>
                                    <select name="" class="form-select form-select-sm">
                                        <option value="" disabled>請選擇</option>
                                        @foreach ($spec->items as $key => $value)
                                            <option value="{{ $value->key }}">{{ $value->value }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            @endforeach

                            <td>
                                <a href="#" class="-text -cost">50</a>
                            </td>
                            <td>
                                <a href="#" class="-text -stock">23</a>
                            </td>
                            <td>
                                <a href="#" class="-text -stock">10</a>
                            </td>
                            <td>
                                <select name="" class="form-select form-select-sm">
                                    <option value="繼續銷售">繼續銷售</option>
                                    <option value="停止銷售">停止銷售</option>
                                    <option value="下架">下架</option>
                                    <option value="預售">預售</option>
                                </select>
                            </td>
                            <td class="text-center">
                                <button type="button" disabled
                                    class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="-cloneElem">
                            <td class="text-center">
                                <div class="form-check form-switch form-switch-lg">
                                    <input class="form-check-input" type="checkbox" checked>
                                </div>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm -l" value="" aria-label="訂單編號"
                                    readonly />
                            </td>
                            <td>
                                <select name="" class="form-select form-select-sm">
                                    <option value="" disabled selected>請選擇</option>
                                    <option value="S">S</option>
                                    <option value="M">M</option>
                                </select>
                            </td>
                            <td>
                                <select name="" class="form-select form-select-sm">
                                    <option value="" disabled selected>請選擇</option>
                                    <option value="紅">紅</option>
                                    <option value="黃">黃</option>
                                </select>
                            </td>
                            <td>
                                <a href="#" class="-text -cost">採購單</a>
                            </td>
                            <td>
                                <a href="#" class="-text -stock">庫存管理</a>
                            </td>
                            <td>
                                <a href="#" class="-text -stock">庫存管理</a>
                            </td>
                            <td>
                                <select name="" class="form-select form-select-sm">
                                    <option value="繼續銷售">繼續銷售</option>
                                    <option value="停止銷售">停止銷售</option>
                                    <option value="下架">下架</option>
                                    <option value="預售">預售</option>
                                </select>
                            </td>
                            <td class="text-center">
                                <button type="button"
                                    class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <button type="button" class="btn btn-primary -newClone">新增款式</button>
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
            #spec_table tbody td>span:not(:first-child)::before {
                content: '、';
            }

        </style>
    @endpush
    @push('sub-scripts')
        <script>
            // clone 項目
            const $clone = $('.-cloneElem:first-child').clone();
            $('.-newClone').off('click').on('click', function() {
                Clone_bindCloneBtn($clone, function(cloneElem) {
                    cloneElem.find('input, select').val('');
                    cloneElem.find('.form-switch input').prop('checked', true);
                    cloneElem.find('a.-text.-cost').text('採購單');
                    cloneElem.find('a.-text.-stock').text('庫存管理');
                    cloneElem.find('.-del').prop('disabled', false);
                });
            });
            Clone_bindDelElem($('.-del'));
        </script>
    @endpush
@endOnce
