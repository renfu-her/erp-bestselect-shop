@extends('layouts.main')
@section('sub-content')
    <div>
        <h2 class="mb-3">{{ $product->title }}</h2>
        <x-b-prd-navi :product="$product"></x-b-prd-navi>
    </div>

    <form id="form1" action="{{ route('cms.product.edit-combo', ['id' => $product->id]) }}" method="POST">
        @csrf
        <div class="card shadow p-4 mb-4">
            <h6>組合包管理</h6>
            <p class="mark m-0"><i class="bi bi-exclamation-diamond-fill mx-2 text-warning"></i>已產生SKU將無法再修改刪除</p>
            <div class="table-responsive tableOverBox">
                <table class="table tableList table-striped">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center">上架</th>
                            <th scope="col" class="text-center">操作</th>
                            <th scope="col" class="text-center">刪除</th>
                            <th scope="col">組合包名稱</th>
                            <th scope="col">SKU
                                <button type="submit" class="btn btn-primary btn-sm -add_sku">產生SKU碼</button>
                                <input type="hidden" name="add_sku" value="0">
                            </th>
                            <th scope="col">售價</th>
                            <th scope="col">經銷價</th>
                            <th scope="col">定價</th>
                            <th scope="col">獎金
                                <i class="bi bi-info-circle" data-bs-toggle="tooltip" title="預設：(售價-經銷價) × 0.97"></i>
                            </th>
                            <th scope="col">庫存</th>
                            <th scope="col">安全庫存</th>
                            <th scope="col">庫存不足</th>
                            <th scope="col">喜鴻紅利抵扣</th>
                        </tr>
                    </thead>
                    <tbody class="-appendClone">
                        @foreach ($styles as $key => $style)
                            <tr class="-cloneElem">
                                <td class="text-center">
                                    <div class="form-check form-switch form-switch-lg">
                                        <input class="form-check-input" type="checkbox" name="active_id[]"
                                            value="{{ $style->id }}" @if ($style->is_active == '1')checked @endif>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if (isset($style->sku))
                                        <a type="button" data-bs-toggle="tooltip" title="內容明細"
                                            href="{{ Route('cms.product.edit-combo-prod', ['id' => $product->id, 'sid' => $style->id]) }}"
                                            class="icon icon-btn fs-5 text-primary rounded-circle border-0 p-0">
                                            <i class="bi bi-card-list"></i>
                                        </a>
                                    @else
                                        <a type="button" data-bs-toggle="tooltip" title="編輯"
                                            href="{{ Route('cms.product.edit-combo-prod', ['id' => $product->id, 'sid' => $style->id]) }}"
                                            class="icon icon-btn fs-5 text-primary rounded-circle border-0 p-0">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button type="button" @if (isset($style->sku)) disabled @endif
                                        class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                                <td>
                                    {{ $style->title }}
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm -l" aria-label="SKU"
                                        value="{{ $style->sku }}" readonly />
                                    <input type="hidden" name="sid[]" value="{{ $style->id }}">
                                </td>
                                <td>
                                    <div class="input-group input-group-sm flex-nowrap">
                                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                        <input type="number" class="form-control form-control-sm" name="price[]" min="0"
                                            value="{{ $style->price }}" required />
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm flex-nowrap">
                                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                        <input type="number" class="form-control form-control-sm" name="dealer_price[]"
                                            min="0" value="{{ $style->dealer_price }}" required />
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm flex-nowrap">
                                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                        <input type="number" class="form-control form-control-sm" name="origin_price[]"
                                            min="0" value="{{ $style->origin_price }}" required />
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm flex-nowrap">
                                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                        <input type="number" class="form-control form-control-sm" name="bonus[]" min="0"
                                            value="{{ $style->bonus }}" required />
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ Route('cms.product.edit-stock', ['id' => $product->id, 'sid' => $style->id]) }}" class="-text -stock">{{ $style->safety_stock }}</a>
                                </td>
                                <td>
                                    <a href="{{ Route('cms.product.edit-stock', ['id' => $product->id, 'sid' => $style->id]) }}" class="-text -stock">{{ $style->in_stock }}</a>
                                </td>
                                <td>
                                    <select name="sold_out_event[]" class="form-select form-select-sm">
                                        <option value="繼續銷售">繼續銷售</option>
                                        <option value="停止銷售">停止銷售</option>
                                        <option value="下架">下架</option>
                                        <option value="預售">預售</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm" name="dividend[]" min="0"
                                        value="{{ $style->dividend }}" required>
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
            // 獎金%數
            const BonusRate = 0.97;
            // del
            let del_id = [];
            Clone_bindDelElem($('.-del'), {
                beforeDelFn: function({
                    $this
                }) {
                    const sid = $this.closest('.-cloneElem').find('input:hidden[name="sid[]"]').val();
                    if (sid) {
                        del_id.push(sid);
                        $('input[name="del_id"]').val(del_id.toString());
                    }
                }
            });

            // 計算 獎金 = (售價-經銷價) × BonusRate
            bindCalculate();
            function bindCalculate() {
                $('input[name="price[]"], input[name="dealer_price[]"]').off('change.bonus')
                .on('change.bonus', function () {
                    const $this = $(this);
                    const price = $this.closest('tr').find('input[name="price[]"]').val() || 0;
                    const dealer_price = $this.closest('tr').find('input[name="dealer_price[]"]').val() || 0;
                    $this.closest('tr').find('input[name="bonus[]"]').val(Math.floor((price - dealer_price) * BonusRate));
                });
            }

            // sku
            $('#form1 button[type="submit"]').on('click.add_sku', function () {
                $('input[name="add_sku"]').val($(this).hasClass('-add_sku') ? 1 : 0);
            });
        </script>
    @endpush
@endOnce
