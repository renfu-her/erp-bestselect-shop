@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">{{ $method == 'create' ? '新增' : '編輯' }}退出單</h2>

    <form id="form1" method="post" action="{{ $form_action }}" class="-banRedo">
        @csrf
        <div class="card shadow p-4 mb-4">
            <h6>採購退出單內容</h6>

            <div class="col-12">
                <label class="form-label">退出單備註</label>
                <input class="form-control" type="text" value="{{ old('memo', $return->memo ?? '') }}" name="memo" placeholder="退出單備註">
            </div>

            <div class="table-responsive tableOverBox mb-3">
                <table id="Pord_list" class="table table-striped tableList">
                    <thead class="small">
                        <tr>
                            <th style="width:3rem;">#</th>
                            <th class="text-center">退出</th>
                            <th>商品名稱</th>
                            <th>SKU</th>
                            <th>退款金額</th>
                            <th>原數量</th>
                            <th class="text-center" style="width: 10%">欲退數量</th>
                            <th>說明</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($main_items as $key => $value)
                            <tr class="--prod">
                                <th scope="row">{{ $key + 1 }}
                                    <input type="hidden" name="m_item_id[]" value="{{ $method == 'create' ? '' : ($value->id) }}" />
                                    <input type="hidden" name="purchase_item_id[]" value="{{ $method == 'create' ? ($value->items_id ?? '') : ($value->purchase_item_id ?? '') }}" />
                                    <input type="hidden" name="product_style_id[]" value="{{ $value->product_style_id ?? '' }}" />
                                    <input type="hidden" name="sku[]" value="{{ $value->sku ?? '' }}" />
                                </th>

                                <td class="text-center">
                                    <input type="hidden" name="show[]" value="{{ old('show.' . $key, $value->show ?? '0') }}">
                                    <input type="checkbox"  class="form-check-input -show" {{ old('show.' . $key, $value->show ?? 0) == 1 ? 'checked' : '' }}>
                                </td>

                                <td>
                                    <input type="text" value="{{ old('product_title.' . $key, $method == 'create' ? ($value->title ?? '') : ($value->product_title ?? '')) }}" name="product_title[]"
                                        class="form-control form-control-sm -l" required readonly>
                                </td>

                                <td>{{ $value->sku ?? '' }}</td>

                                <td>
                                    <input type="number" value="{{ old('price.' . $key, $method == 'create' ? ($value->single_price ?? '') : ($value->price ?? '')) }}" name="price[]"
                                        class="form-control form-control-sm -sm" min="0" step="0.01" required>
                                </td>

                                <td class="text-center">{{ $value->num ? number_format($value->num) : 0 }}</td>

                                <td>
                                    <x-b-qty-adjuster name="back_qty[]" value="{{ old('back_qty.' . $key, $method == 'create' ? '0' : $value->num) }}"
                                                    min="0" max="{{ $method == 'create' ? ($value->num ?? '0') : ($value->p_num ?? '0') }}"
                                                    size="sm" minus="-" plus="+"></x-b-qty-adjuster>
                                </td>

                                <td>
                                    <input type="text" value="{{ old('mmemo.' . $key, $value->memo ?? '') }}" name="mmemo[]" class="form-control form-control-sm -xl">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <h6 class="mb-1">其他項目</h6>

            <div class="table-responsive tableOverBox">
                <table class="table table-sm table-hover tableList mb-1">
                    <thead class="small">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">會計科目</th>
                            <th scope="col">項目</th>
                            <th scope="col">金額（單價）</th>
                            <th scope="col">數量</th>
                            <th scope="col">備註</th>
                        </tr>
                    </thead>

                    <tbody>
                    @php
                        $items = $other_items;
                    @endphp

                    @for ($i = 0; $i < 5; $i++)
                        <tr>
                            <td>{{ $i + 1 }}<input type="hidden" name="o_item_id[{{ $i }}]" value="{{ $items[$i]->id ?? '' }}" class="d-target"></td>

                            <td>
                                <select class="select-check form-select form-select-sm -select2 -single @error('rgrade_id.' . $i) is-invalid @enderror" name="rgrade_id[{{ $i }}]" data-placeholder="請選擇會計科目">
                                    <option value="" selected disabled>請選擇會計科目</option>
                                    @foreach($total_grades as $g_value)
                                        <option value="{{ $g_value['primary_id'] }}" {{ $g_value['primary_id'] == old('rgrade_id.' . $i, $items[$i]->grade_id ?? '') ? 'selected' : '' }}
                                            @if($g_value['grade_num'] === 1)
                                                class="grade_1"
                                            @elseif($g_value['grade_num'] === 2)
                                                class="grade_2"
                                            @elseif($g_value['grade_num'] === 3)
                                                class="grade_3"
                                            @elseif($g_value['grade_num'] === 4)
                                                class="grade_4"
                                            @endif
                                        >{{ $g_value['code'] . ' ' . $g_value['name'] }}</option>
                                    @endforeach
                                </select>
                            </td>

                            <td>
                                <input type="text" name="rtitle[{{ $i }}]"
                                        value="{{ old('rtitle.' . $i, $items[$i]->product_title ?? '') }}"
                                        class="d-target form-control form-control-sm @error('rtitle.' . $i) is-invalid @enderror"
                                        aria-label="項目" placeholder="請輸入項目" disabled>
                            </td>

                            <td>
                                <input type="number" name="rprice[{{ $i }}]"
                                        value="{{ old('rprice.' . $i, $items[$i]->price ?? '') }}"
                                        class="d-target r-target form-control form-control-sm @error('rprice.' . $i) is-invalid @enderror"
                                        aria-label="金額" placeholder="請輸入金額" disabled>
                            </td>

                            <td>
                                <input type="number" name="rqty[{{ $i }}]"
                                        value="{{ old('rqty.' . $i, $items[$i]->qty ?? '') }}" min="0"
                                        class="d-target r-target form-control form-control-sm @error('rqty.' . $i) is-invalid @enderror"
                                        aria-label="數量" placeholder="請輸入數量" disabled>
                            </td>

                            <td>
                                <input type="text" name="rmemo[{{ $i }}]"
                                        value="{{ old('rmemo.' . $i, $items[$i]->memo ?? '') }}"
                                        class="d-target form-control form-control-sm @error('rmemo.' . $i) is-invalid @enderror"
                                        aria-label="備註" placeholder="請輸入備註" disabled>
                            </td>
                        </tr>
                    @endfor
                    </tbody>
                </table>
            </div>

            @if($errors->any())
                <div class="alert alert-danger mt-3">{!! implode('', $errors->all('<div>:message</div>')) !!}</div>
            @endif
        </div>

        <div id="submitDiv">
            <div class="col-auto">
                <input type="hidden" name="method" value="{{ $method }}" />
                <button type="submit" class="btn btn-primary px-4" >送出</button>
                <a href="{{ $back_url }}" class="btn btn-outline-primary px-4" role="button">返回明細</a>
            </div>
        </div>
    </form>
@endsection

@once
    @push('sub-scripts')
        <script>
            // +/- btn
            $('button.-minus, button.-plus').on('click', function() {
                const $input = $(this).siblings('input[type="number"]');
                const max = $input.attr('max') !== '' ? Number($input.attr('max')) : null;
                const min = $input.attr('min') !== '' ? Number($input.attr('min')) : null;
                const m_qty = Number($input.val());
                if ($(this).hasClass('-minus') && (min !== null && m_qty > min)) {
                    $input.val(m_qty - 1);
                }
                if ($(this).hasClass('-plus') && (max != null && m_qty < max)) {
                    $input.val(m_qty + 1);
                }
            });

            $(document).on('change', 'select.select-check', function() {
                if(this.value){
                    $(this).parents('tr').find('.d-target').prop('disabled', false);
                    $(this).parents('tr').find('.r-target').prop('required', true);
                } else {
                    $(this).parents('tr').find('.d-target').prop('disabled', true);
                    $(this).parents('tr').find('.r-target').prop('required', false);
                }
            });

            $.each($('select.select-check'), function(i, ele) {
                if(ele.value){
                    $(ele).parents('tr').find('.d-target').prop('disabled', false);
                    $(ele).parents('tr').find('.r-target').prop('required', true);
                } else {
                    $(ele).parents('tr').find('.d-target').prop('disabled', true);
                    $(ele).parents('tr').find('.r-target').prop('required', false);
                }
            });

            // 退出 checkbox
            $('input.-show').each(function (index, element) {
                // element == this
                const $back_qty = $(element).closest('tr').find('[name="back_qty[]"]');
                const checked = $(element).prop('checked');
                $back_qty.data('max', $back_qty.attr('max'));
                if (checked) {
                    $back_qty.attr({min: 1, max: $back_qty.data('max')});
                } else {
                    $back_qty.attr({min: 0, max: 0});
                }
            });

            $('input.-show').on('change', function () {
                const $this = $(this);
                const checked = $this.prop('checked');
                if (checked) {
                    $this.prev('[name="show[]"]').val(1);
                    const $back_qty = $this.closest('tr').find('[name="back_qty[]"]');
                    $back_qty.attr({min: 1, max: $back_qty.data('max')}).val(1);
                } else {
                    $this.prev('[name="show[]"]').val(0);
                    $this.closest('tr').find('[name="back_qty[]"]')
                    .attr({min: 0, max: 0}).val(0);
                }
            });
        </script>
    @endpush
@endonce
