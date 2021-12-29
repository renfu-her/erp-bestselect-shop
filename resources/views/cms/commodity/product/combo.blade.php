@extends('layouts.main')
@section('sub-content')
    <div>
        <h2 class="mb-3">組合包名稱</h2>
        {{-- <x-b-prd-navi id="$data->id" type="combo"></x-b-prd-navi> --}}
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
                            <th scope="col">SKU <a href=""
                                type="button" class="btn btn-primary btn-sm">產生SKU碼</a></th>
                            <th scope="col">庫存</th>
                            <th scope="col">安全庫存</th>
                            <th scope="col">庫存不足</th>
                            <th scope="col" class="text-center">編輯</th>
                            <th scope="col" class="text-center">刪除</th>
                        </tr>
                    </thead>
                    <tbody class="-appendClone">
                        {{-- @if (count($styles) == 0) --}}
                            <tr class="-cloneElem">
                                <td class="text-center">
                                    <div class="form-check form-switch form-switch-lg">
                                        <input class="form-check-input" type="checkbox" name="n_active[]" checked>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" name="" class="form-control form-control-sm -l" value="">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm -l" value="" aria-label="SKU"
                                        readonly />
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
                                    <button type="button"
                                        class="icon icon-btn fs-5 text-primary rounded-circle border-0 p-0">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                </td>
                                <td class="text-center">
                                    <button type="button"
                                        class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        {{-- @endif --}}
                    </tbody>
                </table>
            </div>
            <div class="d-grid gap-2 mt-3">
                <a href="{{ Route('cms.combo-product.edit-combo-prod', ['id' => 1]) }}" class="btn btn-outline-primary border-dashed" style="font-weight: 500;">
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
            $('.-newClone').off('click').on('click', function() {
                createInitStyle(false);
            });
            // 新增一條款式
            function createInitStyle(items) {
                Clone_bindCloneBtn($clone, function(cloneElem) {
                    cloneElem.find('input, select').val('');
                    cloneElem.find('input:hidden[name$="_style_id[]"]').remove();
                    cloneElem.find('.form-switch input').prop('checked', true);
                    cloneElem.find('a.-text.-cost').text('採購單');
                    cloneElem.find('a.-text.-stock').text('庫存管理');
                    cloneElem.find('select[name$="sold_out_event[]"]').val('繼續銷售');
                    cloneElem.find('select, .-del').prop('disabled', false);
                    cloneElem.find('input[name="active_id[]"]').attr('name', 'n_active[]');
                    cloneElem.find('select[name]').attr('name', function(index, attr) {
                        return attr.replace(/nsk_|sk_/, 'n_');
                    });
                    if (items) {
                        cloneElem.find('select[name="n_spec1[]"]').val(items.spec_item1_id);
                        cloneElem.find('select[name="n_spec2[]"]').val(items.spec_item2_id);
                        cloneElem.find('select[name="n_spec3[]"]').val(items.spec_item3_id);
                    }
                });
            }
            // del
            let del_id = [];
            Clone_bindDelElem($('.-del'));
            $('.-del').off('click.del-id').on('click.del-id', function() {
                const style_id = $(this).closest('.-cloneElem').find('input:hidden[name="nsk_style_id[]"]').val();
                if (style_id) {
                    del_id.push(style_id);
                    $('input[name="del_id"]').val(del_id.toString());
                }
            });
        </script>
    @endpush
@endOnce
