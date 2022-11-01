@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">{{ $method == 'create' ? '新增' : '編輯' }}轉帳傳票</h2>

    <form id="form1" method="POST" action="{{ $form_action }}">
        @csrf
        <div class="card shadow p-4 mb-4">
            <div class="row">
                <div class="col-12">
                    <label class="form-label">傳票日期 <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('voucher_date') is-invalid @enderror" name="voucher_date" value="{{ old('voucher_date', date('Y-m-d', strtotime($voucher->tv_voucher_date ?? date('Y-m-d'))) ) }}" aria-label="傳票日期" required>
                    <div class="invalid-feedback">
                        @error('voucher_date')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow p-4 mb-4">
            <h6>轉帳傳票項目</h6>
            
            <div class="table-responsive tableOverBox">
                <table class="table table-sm table-hover tableList mb-1">
                    <thead class="small">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">會計科目</th>
                            <th scope="col">摘要說明</th>
                            <th scope="col" class="text-center">借貸</th>
                            <th scope="col">金額</th>
                            <th scope="col">幣別</th>
                            <th scope="col">匯率</th>
                            <th scope="col">部門</th>
                            <th scope="col">備註</th>
                        </tr>
                    </thead>

                    <tbody class="-serial-number -appendClone">
                        @if ($method !== 'create')
                            @php
                                $items = json_decode($voucher->tv_items) ?? [];
                            @endphp
                            @foreach ($items as $i => $item)
                                <tr>
                                    <th scope="row">
                                        <span class="-serial-title -after"></span>
                                        <input type="hidden" data-i="{{ $i }}" name="tv_item_id[{{ $i }}]" value="{{ $item->id ?? '' }}">
                                    </th>
                                    <td>
                                        <select class="select-check form-select form-select-sm -select2 -single 
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
                                        <input type="text" name="summary[{{ $i }}]" 
                                            class="d-target form-control form-control-sm -l @error('summary.' . $i) is-invalid @enderror" 
                                            value="{{ old('summary.' . $i, $item->summary ?? '') }}" 
                                            aria-label="摘要說明" placeholder="摘要說明" disabled>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check lh-base">
                                            <label class="form-check-label">
                                                <input type="radio" name="debit_credit_code[{{ $i }}]" value="debit" 
                                                    class="d-target r-target form-check-input @error('debit_credit_code.' . $i) is-invalid @enderror" 
                                                    {{ old('debit_credit_code.' . $i, $item->debit_credit_code ?? '') == 'debit' ? 'checked' : '' }} 
                                                    disabled>
                                                借
                                            </label>
                                        </div>
                                        <div class="form-check lh-base">
                                            <label class="form-check-label">
                                                <input type="radio" name="debit_credit_code[{{ $i }}]" value="credit"
                                                    class="d-target r-target form-check-input @error('debit_credit_code.' . $i) is-invalid @enderror" 
                                                    {{ old('debit_credit_code.' . $i, $item->debit_credit_code ?? '') == 'credit' ? 'checked' : '' }} 
                                                    disabled>
                                                貸
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" name="currency_price[{{ $i }}]" 
                                            value="{{ old('currency_price.' . $i, $item->currency_price ?? '') }}" 
                                            class="d-target r-target form-control form-control-sm @error('currency_price.' . $i) is-invalid @enderror" 
                                            aria-label="金額" placeholder="金額" disabled>
                                    </td>
                                    <td>
                                        <select class="d-target form-select form-select-sm -single2 @error('currency_id.' . $i) is-invalid @enderror" 
                                            name="currency_id[{{ $i }}]" data-placeholder="NTD-新台幣" disabled>
                                            <option value="" selected disabled>請選擇幣別</option>
                                            @foreach($currency as $value)
                                                <option value="{{ $value->id }}" {{ $value->id == old('currency_id.' . $i, $item->currency_id ?? '') ? 'selected' : '' }}>{{ $value->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="rate[{{ $i }}]" step="0.01" 
                                            value="{{ old('rate.' . $i, $item->rate ?? '1') }}" 
                                            class="d-target r-target form-control form-control-sm -sx @error('rate.' . $i) is-invalid @enderror" 
                                            aria-label="匯率" placeholder="匯率" disabled>
                                    </td>
                                    <td>
                                        <select class="d-target form-select form-select-sm -single2 @error('department.' . $i) is-invalid @enderror" name="department[{{ $i }}]" data-placeholder="請選擇部門" disabled>
                                            <option value="" selected disabled>請選擇部門</option>
                                            @foreach($department as $value)
                                                <option value="{{ $value }}" {{ $value == old('department.' . $i, $item->department ?? auth('user')->user()->department) ? 'selected' : '' }}>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="memo[{{ $i }}]" 
                                            value="{{ old('memo.' . $i, $item->memo ?? '') }}" 
                                            class="d-target form-control form-control-sm -l @error('memo.' . $i) is-invalid @enderror" 
                                            aria-label="備註" placeholder="備註" disabled>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                        <tr class="-cloneElem">
                            <th scope="row">
                                <span class="-serial-title -after"></span>
                                <input type="hidden" name="tv_item_id[]" value="">
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
                                <input type="text" name="summary[]" value="" 
                                    class="d-target form-control form-control-sm -l" 
                                    aria-label="摘要說明" placeholder="摘要說明" disabled>
                            </td>
                            <td class="text-center">
                                <div class="form-check lh-base">
                                    <label class="form-check-label">
                                        <input type="radio" name="debit_credit_code[]" value="debit" 
                                            class="d-target r-target form-check-input" 
                                            disabled>
                                        借
                                    </label>
                                </div>
                                <div class="form-check lh-base">
                                    <label class="form-check-label">
                                        <input type="radio" name="debit_credit_code[]" value="credit"
                                            class="d-target r-target form-check-input" 
                                            disabled>
                                        貸
                                    </label>
                                </div>
                            </td>
                            <td>
                                <input type="number" name="currency_price[]" value="" 
                                    class="d-target r-target form-control form-control-sm" 
                                    aria-label="金額" placeholder="金額" disabled>
                            </td>
                            <td>
                                <select class="d-target form-select form-select-sm -single2" 
                                    name="currency_id[]" data-placeholder="NTD-新台幣" disabled>
                                    <option value="" selected disabled>請選擇幣別</option>
                                    @foreach($currency as $value)
                                        <option value="{{ $value->id }}">{{ $value->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" name="rate[]" step="0.01" value="1" 
                                    class="d-target r-target form-control form-control-sm -sx" 
                                    aria-label="匯率" placeholder="匯率" disabled>
                            </td>
                            <td>
                                <select class="d-target form-select form-select-sm -single2" 
                                    name="department[]" data-placeholder="請選擇部門" disabled>
                                    <option value="" selected disabled>請選擇部門</option>
                                    @foreach($department as $value)
                                        <option value="{{ $value }}" {{ $value == auth('user')->user()->department ? 'selected' : '' }}>{{ $value }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="memo[]" value="" 
                                    class="d-target form-control form-control-sm -l" 
                                    aria-label="備註" placeholder="備註" disabled>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="9">
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
                    Clone_bindCloneBtn($clone, function($c) {
                        const len = $('input[name^="debit_credit_code"]').length / 2;
                        console.log(len);
                        $c.find('input[name="debit_credit_code[]"]').attr('name', `debit_credit_code[${len}]`);
                    });
                    bindSelectEvent();
                });
                
                // submit
                $('#form1').submit(function (e) {
                    const  $input = $('input[name^=tv_item_id][data-i]:last');
                    let i = $input.length > 0 ? $input.data('i') : -1;
                    $('input[name="tv_item_id[]"]').each(function (index, element) {
                        i++;
                        // element == this
                        $(element).attr('name', `tv_item_id[${i}]`);
                        const $tr = $(element).closest('tr');
                        $tr.find('select[name="grade_id[]"]').attr('name', `grade_id[${i}]`);
                        $tr.find('input[name="summary[]"]').attr('name', `summary[${i}]`);
                        $tr.find('input[name^="debit_credit_code"]').attr('name', `debit_credit_code[${i}]`);
                        $tr.find('input[name="currency_price[]"]').attr('name', `currency_price[${i}]`);
                        $tr.find('select[name="currency_id[]"]').attr('name', `currency_id[${i}]`);
                        $tr.find('input[name="rate[]"]').attr('name', `rate[${i}]`);
                        $tr.find('input[name="department[]"]').attr('name', `department[${i}]`);
                        $tr.find('input[name="memo[]"]').attr('name', `memo[${i}]`);
                    });
                });
            });
        </script>
    @endpush
@endonce