@extends('layouts.main')
@section('sub-content')
    <div>
        <h2 class="mb-3">{{ $product->title }}</h2>
        <x-b-prd-navi :product="$product"></x-b-prd-navi>
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
                                @foreach ($spec->items as $item)
                                    <span class="badge rounded-pill bg-secondary me-1">{{ $item->value }}</span>
                                @endforeach
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

    <form id="form1" action="{{ route('cms.product.edit-style', ['id' => $data->id]) }}" method="POST">
        @csrf
        <div class="card shadow p-4 mb-4">
            <h6>款式管理</h6>
            @if (count($specList) == 0)
                <p class="mark"><i class="bi bi-exclamation-diamond-fill mx-2 text-warning"></i> 尚無款式，請先至規格管理新增規格
                </p>
            @else
                <div class="table-responsive tableOverBox">
                    <table class="table tableList table-hover mb-1">
                        <thead>
                            <tr>
                                <th scope="col" class="text-center">上架</th>
                                <th scope="col" class="text-center">刪除</th>
                                <th scope="col">SKU
                                    <button type="submit" class="btn btn-primary btn-sm -add_sku">產生SKU碼</button>
                                    <input type="hidden" name="add_sku" value="0">
                                </th>
                                @foreach ($specList as $key => $spec)
                                    <th scope="col">{{ $spec->title }}</th>
                                @endforeach
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
                            <tr class="-cloneElem d-none">
                                <td class="text-center">
                                    <div class="form-check form-switch form-switch-lg">
                                        <input class="form-check-input" type="checkbox" name="n_active[]" checked>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button type="button"
                                        class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm -l" value="" aria-label="SKU"
                                        readonly />
                                </td>

                                @foreach ($specList as $specKey => $spec)
                                    <td>
                                        <select name="n_spec{{ $specKey + 1 }}[]" class="form-select form-select-sm"
                                            required>
                                            <option value="" disabled selected>請選擇</option>
                                            @foreach ($spec->items as $key => $value)
                                                <option value="{{ $value->key }}">
                                                    {{ $value->value }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                @endforeach
                                <td>
                                    <div class="input-group input-group-sm flex-nowrap">
                                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                        <input type="number" class="form-control form-control-sm" name="n_price[]" min="0"
                                            value="0" required />
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm flex-nowrap">
                                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                        <input type="number" class="form-control form-control-sm" name="n_dealer_price[]"
                                            min="0" value="0" required />
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm flex-nowrap">
                                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                        <input type="number" class="form-control form-control-sm" name="n_origin_price[]"
                                            min="0" value="0" required />
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm flex-nowrap">
                                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                        <input type="number" class="form-control form-control-sm" name="n_bonus[]" min="0"
                                            value="0" required />
                                    </div>
                                </td>
                                <td>
                                    <a href="#" class="-text -stock">庫存管理</a>
                                </td>
                                <td>
                                    <a href="#" class="-text -stock">庫存管理</a>
                                </td>
                                <td>
                                    <select name="n_sold_out_event[]" class="form-select form-select-sm">
                                        <option value="繼續銷售" selected>繼續銷售</option>
                                        <option value="停止銷售">停止銷售</option>
                                        <option value="下架">下架</option>
                                        <option value="預售">預售</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm" name="n_dividend[]" min="0"
                                        value="" required>
                                </td>
                            </tr>
                            @foreach ($styles as $styleKey => $style)
                                @php
                                    $prefix = $style->sku ? 'sk_' : 'nsk_';
                                @endphp
                                <tr class="-cloneElem">
                                    <td class="text-center">
                                        <div class="form-check form-switch form-switch-lg">
                                            <input class="form-check-input" name="active_id[]" value="{{ $style->id }}"
                                                type="checkbox" @if ($style->is_active) checked @endif>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" @if (isset($style->sku)) disabled @endif
                                            class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm -l"
                                            value="{{ $style->sku }}" aria-label="SKU" readonly />
                                        <input type="hidden" name="{{ $prefix }}style_id[]"
                                            value="{{ $style->id }}">
                                    </td>

                                    @foreach ($specList as $specKey => $spec)
                                        <td>
                                            @if (isset($style->sku)) 
                                                <div class="form-control form-control-sm" readonly>
                                                    {{ $style->{'spec_item' . ($specKey + 1) . '_title'} }}
                                                </div>
                                            @else
                                                <select name="{{ $prefix }}spec{{ $specKey + 1 }}[]" class="form-select form-select-sm" required>
                                                    <option value="" disabled>請選擇</option>
                                                    @foreach ($spec->items as $key => $value)
                                                        <option value="{{ $value->key }}" @if ($value->key == $style->{'spec_item' . ($specKey + 1) . '_id'}) selected @endif>
                                                            {{ $value->value }}</option>
                                                    @endforeach
                                                </select>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td>
                                        <div class="input-group input-group-sm flex-nowrap">
                                            <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                            <input type="number" class="form-control form-control-sm" name="{{ $prefix }}price[]" min="0"
                                                value="{{ $style->price }}" required />
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm flex-nowrap">
                                            <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                            <input type="number" class="form-control form-control-sm" name="{{ $prefix }}dealer_price[]"
                                                min="0" value="{{ $style->dealer_price }}" required />
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm flex-nowrap">
                                            <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                            <input type="number" class="form-control form-control-sm" name="{{ $prefix }}origin_price[]"
                                                min="0" value="{{ $style->origin_price }}" required />
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm flex-nowrap">
                                            <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                            <input type="number" class="form-control form-control-sm" name="{{ $prefix }}bonus[]" min="0"
                                                value="{{ $style->bonus }}" required />
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ Route('cms.product.edit-stock', ['id' => $data->id, 'sid' => $style->id]) }}" class="-text -stock">{{ $style->in_stock }}</a>
                                    </td>
                                    <td>
                                        <a href="{{ Route('cms.product.edit-stock', ['id' => $data->id, 'sid' => $style->id]) }}" class="-text -stock">{{ $style->safety_stock }}</a>
                                    </td>
                                    <td>
                                        <select name="{{ $prefix }}sold_out_event[]"
                                            class="form-select form-select-sm">
                                            <option value="繼續銷售">繼續銷售</option>
                                            <option value="停止銷售">停止銷售</option>
                                            <option value="下架">下架</option>
                                            <option value="預售">預售</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm" name="{{ $prefix }}dividend[]" min="0"
                                            value="" required>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-grid gap-2 mt-3">
                    <button type="button" class="btn btn-outline-primary border-dashed -newClone" style="font-weight: 500;">
                        <i class="bi bi-plus-circle"></i> 新增款式
                    </button>
                </div>
            @endif
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
            table .badge.rounded-pill {
                font-size: .94rem;
                font-weight: 400;
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            // 獎金%數
            const BonusRate = 0.97;
            const initStyles = @json($initStyles);

            // clone 項目
            const $clone = $('.-cloneElem:first-child').clone();
            $('.-cloneElem.d-none').remove();

            // 新增一條款式
            $('.-newClone').off('click').on('click', function() {
                Clone_bindCloneBtn($clone, function () {  });
                bindCalculate();
            });
            initStyles.forEach(style => {
                Clone_bindCloneBtn($clone, function(cloneElem) {
                    if (style) {
                        cloneElem.find('select[name="n_spec1[]"]').val(style.spec_item1_id);
                        cloneElem.find('select[name="n_spec2[]"]').val(style.spec_item2_id);
                        cloneElem.find('select[name="n_spec3[]"]').val(style.spec_item3_id);
                    }
                });
                bindCalculate();
            });

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

            // 計算 獎金 = (售價-經銷價) × BonusRate
            bindCalculate();
            function bindCalculate() {
                $('input[name$="_price[]"], input[name$="_dealer_price[]"]').off('change.bonus')
                .on('change.bonus', function () {
                    const $this = $(this);
                    const price = $this.closest('tr').find('input[name$="_price[]"]').val() || 0;
                    const dealer_price = $this.closest('tr').find('input[name$="_dealer_price[]"]').val() || 0;
                    $this.closest('tr').find('input[name$="_bonus[]"]').val(Math.floor((price - dealer_price) * BonusRate));
                });
            }

            // sku
            $('#form1 button[type="submit"]').on('click.add_sku', function () {
                $('input[name="add_sku"]').val($(this).hasClass('-add_sku') ? 1 : 0);
            });
            
        </script>
    @endpush
@endOnce
