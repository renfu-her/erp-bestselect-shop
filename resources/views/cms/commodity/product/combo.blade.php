@extends('layouts.main')
@section('sub-content')
    <div>
        <h2 class="mb-3">{{ $product->title }}</h2>
        <x-b-prd-navi :product="$product"></x-b-prd-navi>
    </div>

    <form action="">
        @csrf
        <div class="card shadow p-4 mb-4">
            <h6>組合包管理</h6>
            <p class="mark m-0"><i class="bi bi-exclamation-diamond-fill mx-2 text-warning"></i>已產生SKU將無法再......</p>
            <div class="table-responsive tableOverBox">
                <table class="table tableList table-striped">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center">上架</th>
                            <th scope="col">組合包名稱</th>
                            <th scope="col">SKU <a href="" type="button" class="btn btn-primary btn-sm">產生SKU碼</a></th>
                            <th scope="col">庫存</th>
                            <th scope="col">安全庫存</th>
                            <th scope="col">庫存不足</th>
                            <th scope="col" class="text-center">編輯</th>
                            <th scope="col" class="text-center">刪除</th>
                        </tr>
                    </thead>
                    <tbody class="-appendClone">
                        @if (count($styles) == 0)
                            <tr class="-cloneElem d-none">
                                <td class="text-center">
                                    <div class="form-check form-switch form-switch-lg">
                                        <input class="form-check-input" type="checkbox" name="n_active[]" checked>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" name="" class="form-control form-control-sm -l" value="">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm -l" value=""
                                        aria-label="SKU" readonly />
                                </td>
                                <td>
                                    <a href="#" class="-text -stock">庫存管理</a>
                                </td>
                                <td>
                                    <a href="#" class="-text -stock">庫存管理</a>
                                </td>
                                <td>
                                    <select name="n_sold_out_event[]" class="form-select form-select-sm">
                                        <option value="繼續銷售">繼續銷售</option>
                                        <option value="停止銷售">停止銷售</option>
                                        <option value="下架">下架</option>
                                        <option value="預售">預售</option>
                                    </select>
                                </td>
                                <td class="text-center">
                                    <a type="button"
                                        href="#"
                                        class="icon icon-btn fs-5 text-primary rounded-circle border-0 p-0">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>
                                <td class="text-center">
                                    <button type="button"
                                        class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endif
                        @foreach ($styles as $key => $style)
                            <tr class="-cloneElem">
                                <td class="text-center">
                                    <div class="form-check form-switch form-switch-lg">
                                        <input class="form-check-input" type="checkbox" name="active_id[]"
                                            value="{{ $style['id'] }}" checked>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" name="" class="form-control form-control-sm -l"
                                        value="">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm -l"
                                        aria-label="SKU" value="{{ $style['sku'] }}" readonly />
                                    <input type="hidden" name="sid" value="{{ $style['id'] }}">
                                </td>
                                <td>
                                    <a href="#" class="-text -stock">{{ $style['safety_stock'] }}</a>
                                </td>
                                <td>
                                    <a href="#" class="-text -stock">{{ $style['in_stock'] }}</a>
                                </td>
                                <td>
                                    <select name="_sold_out_event[]" class="form-select form-select-sm">
                                        <option value="繼續銷售">繼續銷售</option>
                                        <option value="停止銷售">停止銷售</option>
                                        <option value="下架">下架</option>
                                        <option value="預售">預售</option>
                                    </select>
                                </td>
                                <td class="text-center">
                                    <a type="button" @if (isset($style['sku'])) disabled @endif
                                        href="{{ Route('cms.product.edit-combo-prod', ['id' => $product->id, 'sid' => $style['id']]) }}"
                                        class="icon icon-btn fs-5 text-primary rounded-circle border-0 p-0">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>
                                <td class="text-center">
                                    <button type="button" @if (isset($style['sku'])) disabled @endif
                                        class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-grid gap-2 mt-3">
                <a href="{{ Route('cms.product.create-combo-prod', ['id' => $product->id]) }}"
                    class="btn btn-outline-primary border-dashed" style="font-weight: 500;">
                    <i class="bi bi-plus-circle"></i> 新增組合款式
                </a>
            </div>
        </div>

        <div>
            <div class="col-auto">
                <input type="hidden" name="del_id">
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
            // clone 項目
            const $clone = $('.-cloneElem:first-child').clone();
            $('.-cloneElem.d-none').remove();
            
            // del
            let del_id = [];
            Clone_bindDelElem($('.-del'), {
                beforeDelFn: function ({$this}) {
                    const sid = $this.closest('.-cloneElem').find('input:hidden[name="sid"]').val();
                    if (sid) {
                        del_id.push(sid);
                        $('input[name="del_id"]').val(del_id.toString());
                    }
                }
            });
        </script>
    @endpush
@endOnce
