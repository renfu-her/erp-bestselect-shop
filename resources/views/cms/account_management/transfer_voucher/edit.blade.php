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

                    <tbody>
                        @php
                            if($method == 'create'){
                                $items = [];
                            } else {
                                $items = json_decode($voucher->tv_items) ?? [];
                            }
                        @endphp
                        @for ($i = 0; $i < 10; $i++)
                        <tr>
                            <td>{{ $i + 1 }}<input type="hidden" name="tv_item_id[{{ $i }}]" value="{{ $items[$i]->id ?? '' }}"></td>

                            <td>
                                <select class="select-check form-select form-select-sm -select2 -single @error('grade_id.' . $i) is-invalid @enderror" name="grade_id[{{ $i }}]" data-placeholder="請選擇會計科目">
                                    <option value="" selected disabled>請選擇會計科目</option>
                                    @foreach($total_grades as $g_value)
                                        <option value="{{ $g_value['primary_id'] }}" {{ $g_value['primary_id'] == old('grade_id.' . $i, $items[$i]->grade_id ?? '') ? 'selected' : '' }}
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
                                    class="d-target form-control form-control-sm @error('summary.' . $i) is-invalid @enderror" 
                                    value="{{ old('summary.' . $i, $items[$i]->summary ?? '') }}" 
                                    aria-label="摘要說明" placeholder="摘要說明" disabled>
                            </td>

                            <td class="text-center">
                                <div class="form-check form-check-inline lh-base">
                                    <label class="form-check-label">
                                        <input type="radio" name="debit_credit_code[{{ $i }}]" value="debit" 
                                            class="d-target r-target form-check-input @error('debit_credit_code.' . $i) is-invalid @enderror" 
                                            {{ old('debit_credit_code.' . $i, $items[$i]->debit_credit_code ?? '') == 'debit' ? 'checked' : '' }} 
                                            disabled>
                                        借
                                    </label>
                                </div>

                                <div class="form-check form-check-inline lh-base">
                                    <label class="form-check-label">
                                        <input type="radio" name="debit_credit_code[{{ $i }}]" value="credit"
                                            class="d-target r-target form-check-input @error('debit_credit_code.' . $i) is-invalid @enderror" 
                                            {{ old('debit_credit_code.' . $i, $items[$i]->debit_credit_code ?? '') == 'credit' ? 'checked' : '' }} 
                                            disabled>
                                        貸
                                    </label>
                                </div>
                            </td>

                            <td>
                                <input type="number" name="currency_price[{{ $i }}]" 
                                    value="{{ old('currency_price.' . $i, $items[$i]->currency_price ?? '') }}" 
                                    class="d-target r-target form-control form-control-sm @error('currency_price.' . $i) is-invalid @enderror" 
                                    aria-label="金額" placeholder="金額" disabled>
                            </td>

                            <td>
                                <select class="d-target form-select form-select-sm -select2 -single @error('currency_id.' . $i) is-invalid @enderror" name="currency_id[{{ $i }}]" data-placeholder="NTD-新台幣" disabled>
                                    <option value="" selected disabled>請選擇幣別</option>
                                    @foreach($currency as $value)
                                        <option value="{{ $value->id }}" {{ $value->id == old('currency_id.' . $i, $items[$i]->currency_id ?? '') ? 'selected' : '' }}>{{ $value->name }}</option>
                                    @endforeach
                                </select>
                            </td>

                            <td>
                                <input type="number" name="rate[{{ $i }}]" step="0.01" 
                                    value="{{ old('rate.' . $i, $items[$i]->rate ?? '1') }}" 
                                    class="d-target r-target form-control form-control-sm @error('rate.' . $i) is-invalid @enderror" 
                                    aria-label="匯率" placeholder="匯率" disabled>
                            </td>

                            <td>
                                <select class="d-target form-select form-select-sm -select2 -single @error('department.' . $i) is-invalid @enderror" name="department[{{ $i }}]" data-placeholder="請選擇部門" disabled>
                                    <option value="" selected disabled>請選擇部門</option>
                                    @foreach($department as $value)
                                        <option value="{{ $value }}" {{ $value == old('department.' . $i, $items[$i]->department ?? auth('user')->user()->department) ? 'selected' : '' }}>{{ $value }}</option>
                                    @endforeach
                                </select>
                            </td>

                            <td>
                                <input type="text" name="memo[{{ $i }}]" 
                                    value="{{ old('memo.' . $i, $items[$i]->memo ?? '') }}" 
                                    class="d-target form-control form-control-sm @error('memo.' . $i) is-invalid @enderror" 
                                    aria-label="備註" placeholder="備註" disabled>
                            </td>
                        </tr>
                        @endfor
                    </tbody>
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
    </style>
    @endpush

    @push('sub-scripts')
        <script>
            $(function() {
                $('.-select2').select2({
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
            });
        </script>
    @endpush
@endonce