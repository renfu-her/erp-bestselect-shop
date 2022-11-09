@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">{{ $method == 'create' ? '新增' : '編輯' }}代墊單</h2>

    <form id="form1" method="POST" action="{{ $form_action }}">
        @csrf
        <div class="card shadow p-4 mb-4">
            <div class="row">
                <div class="col-12">
                    <label class="form-label">客戶 <span class="text-danger">*</span></label>
                    <select class="form-select -select2 -single" name="client_key" aria-label="客戶" data-placeholder="請選擇客戶" required>
                        <option value="" selected disabled>請選擇</option>
                        @foreach ($client as $value)
                            @php
                                $client_key = '';
                                if(isset($stitute_order)){
                                    $client_name = explode(' - ', $stitute_order->so_client_name);
                                    $client_key = $stitute_order->so_client_id . '|' . $client_name[0];
                                }
                            @endphp
                            <option value="{{ $value['id'] . '|' . $value['name'] }}" {{ $value['id'] . '|' . $value['name'] == old('client_key', $client_key) ? 'selected' : '' }}>{{ $value['name'] . ' - ' . (isset($value['title']) ? $value['title'] . ' ' : '') . ($value['email'] ?? $value['id']) }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">
                        @error('client_key')
                        {{ $message }}
                        @enderror
                    </div>
                </div>

                {{--
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">幣別 <span class="text-danger">*</span></label>
                    <select class="form-select -select2 -single" name="currency_id" aria-label="幣別" data-placeholder="請選擇幣別" required>
                        @foreach ($currency as $value)
                            <option value="{{ $value->id }}" {{ $value->id == old('currency_id') ? 'selected' : '' }}>{{ $value->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">匯率 <span class="text-danger">*</span></label>
                    <input type="number" name="rate" class="form-control @error('rate') is-invalid @enderror" value="{{ old('rate', 1) }}" placeholder="請輸入匯率" data-placeholder="匯率">
                    <div class="invalid-feedback">
                        @error('rate')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
                --}}
            </div>
        </div>

        <div class="card shadow p-4 mb-4">
            <h6>代墊單項目</h6>

            <div class="table-responsive tableOverBox">
                <table class="table table-sm table-hover tableList mb-1">
                    <thead class="small">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">會計科目</th>
                            <th scope="col">金額（單價）</th>
                            <th scope="col">數量</th>
                            <th scope="col">摘要</th>
                            <th scope="col">備註</th>
                            {{--
                            <th scope="col" class="text-center">借貸</th>
                            <th scope="col">幣別</th>
                            <th scope="col">匯率</th>
                            --}}
                        </tr>
                    </thead>

                    <tbody class="-serial-number -appendClone">
                        @if ($method !== 'create')
                            @php
                                $items = json_decode($stitute_order->so_items) ?? [];
                            @endphp
                            @foreach ($items as $i => $item)
                                <tr>
                                    <th scope="row">
                                        <span class="-serial-title -after"></span>
                                        <input type="hidden" data-i="{{ $i }}" name="so_item_id[{{ $i }}]" value="{{ $item->id ?? '' }}">
                                    </th>
                                    <td>
                                        <select class="select-check form-select form-select-sm -single2
                                            @error('grade_id.' . $i) is-invalid @enderror" name="grade_id[{{ $i }}]" 
                                            data-placeholder="請選擇會計科目">
                                            <option value="" selected disabled>請選擇會計科目</option>
                                            @foreach($total_grades as $g_value)
                                                <option value="{{ $g_value['primary_id'] }}" {{ $g_value['primary_id'] == old('grade_id.' . $i, $item->grade_id ?? '') ? 'selected' : '' }}
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
                                        <input type="number" name="price[{{ $i }}]" 
                                            value="{{ old('price.' . $i, $item->price ?? '') }}" min="0" 
                                            class="d-target r-target form-control form-control-sm @error('price.' . $i) is-invalid @enderror" 
                                            aria-label="金額" placeholder="請輸入金額" disabled>
                                    </td>
                                    <td>
                                        <input type="number" name="qty[{{ $i }}]" 
                                            value="{{ old('qty.' . $i, $item->qty ?? '') }}" min="0" 
                                            class="d-target r-target form-control form-control-sm -sx @error('qty.' . $i) is-invalid @enderror" 
                                            aria-label="數量" placeholder="請輸入數量" disabled>
                                    </td>
                                    <td>
                                        <input type="text" name="summary[{{ $i }}]" 
                                            class="d-target form-control form-control-sm -l @error('summary.' . $i) is-invalid @enderror" 
                                            value="{{ old('summary.' . $i, $item->summary ?? '') }}" 
                                            aria-label="摘要" placeholder="請輸入摘要" disabled>
                                    </td>
                                    <td>
                                        <input type="text" name="memo[{{ $i }}]" 
                                            value="{{ old('memo.' . $i, $item->memo ?? '') }}" 
                                            class="d-target form-control form-control-sm -l @error('memo.' . $i) is-invalid @enderror" 
                                            aria-label="備註" placeholder="請輸入備註" disabled>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                        <tr class="-cloneElem">
                            <th scope="row">
                                <span class="-serial-title -after"></span>
                                <input type="hidden" name="so_item_id[]" value="">
                            </th>
                            <td>
                                <select class="select-check form-select form-select-sm -single2" 
                                    name="grade_id[]" data-placeholder="請選擇會計科目">
                                    <option value="" selected disabled>請選擇會計科目</option>
                                    @foreach($total_grades as $g_value)
                                        <option value="{{ $g_value['primary_id'] }}"
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
                                <input type="number" name="price[]" value="" min="0" 
                                    class="d-target r-target form-control form-control-sm" 
                                    aria-label="金額" placeholder="請輸入金額" disabled>
                            </td>
                            <td>
                                <input type="number" name="qty[]" value="" min="0" 
                                    class="d-target r-target form-control form-control-sm -sx" 
                                    aria-label="數量" placeholder="請輸入數量" disabled>
                            </td>
                            <td>
                                <input type="text" name="summary[]" value="" 
                                    class="d-target form-control form-control-sm -l" 
                                    aria-label="摘要" placeholder="請輸入摘要" disabled>
                            </td>
                            <td>
                                <input type="text" name="memo[]" value="" 
                                    class="d-target form-control form-control-sm -l" 
                                    aria-label="備註" placeholder="請輸入備註" disabled>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6">
                                <div class="d-grid gap-2 mt-3">
                                    <button type="button" class="btn btn-outline-primary border-dashed -newClone" style="font-weight: 500;">
                                        <i class="bi bi-plus-circle"></i> 新增項目
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">確認</button>
            <a href="{{ url()->previous() }}" class="btn btn-outline-primary px-4" role="button">取消</a>
        </div>
    </form>
@endsection

@once
    @push('sub-styles')
        <style>
            /*
            .grade_1 {
                padding-left: 1ch;
            }

            .grade_2 {
                padding-left: 2ch;
            }

            .grade_3 {
                padding-left: 4ch;
            }

            .grade_4 {
                padding-left: 8ch;
            }
            */
            select.-single2 {
                display: none;
            }
        </style>
    @endpush

    @push('sub-scripts')
        <script>
            $(function() {
                // clone 項目
                const $clone = $('.-cloneElem').removeClass('-cloneElem').clone();
                bindSelectEvent();

                function bindSelectEvent() {
                    $('select.-single2:not(.select2-hidden-accessible)').select2({
                        templateResult: function (data) {
                            if (!data.element) {
                                return data.text;
                            }

                            var $element = $(data.element);

                            var $wrapper = $('<span></span>');
                            $wrapper.addClass($element[0].className);

                            $wrapper.text(data.text);

                            return $wrapper;
                        }
                    });

                    $(document).off('change', 'select.select-check')
                    .on('change', 'select.select-check', function() {
                        if(this.value){
                            $(this).parents('tr').find('.d-target').prop('disabled', false);
                            $(this).parents('tr').find('.r-target').prop('required', true);
                        } else {
                            $(this).parents('tr').find('.d-target').prop('disabled', true);
                            $(this).parents('tr').find('.r-target').prop('required', false);
                        }
                    });
                }
                
                $.each($('select.select-check'), function(i, ele) {
                    if(ele.value){
                        $(ele).parents('tr').find('.d-target').prop('disabled', false);
                        $(ele).parents('tr').find('.r-target').prop('required', true);
                    } else {
                        $(ele).parents('tr').find('.d-target').prop('disabled', true);
                        $(ele).parents('tr').find('.r-target').prop('required', false);
                    }
                });

                // 新增項目
                $('.-newClone').off('click').on('click', function() {
                    Clone_bindCloneBtn($clone, function() {});
                    bindSelectEvent();
                });

                // submit
                $('#form1').submit(function (e) {
                    const  $input = $('input[name^=so_item_id][data-i]:last');
                    let i = $input.length > 0 ? $input.data('i') : -1;
                    $('input[name="so_item_id[]"]').each(function (index, element) {
                        i++;
                        // element == this
                        $(element).attr('name', `so_item_id[${i}]`);
                        const $tr = $(element).closest('tr');
                        $tr.find('select[name="grade_id[]"]').attr('name', `grade_id[${i}]`);
                        $tr.find('input[name="price[]"]').attr('name', `price[${i}]`);
                        $tr.find('input[name="qty[]"]').attr('name', `qty[${i}]`);
                        $tr.find('input[name="summary[]"]').attr('name', `summary[${i}]`);
                        $tr.find('input[name="memo[]"]').attr('name', `memo[${i}]`);
                    });
                });
            });
        </script>
    @endpush
@endonce