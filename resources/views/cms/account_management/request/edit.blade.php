@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">{{ $method == 'create' ? '新增' : '編輯' }}請款單</h2>

    <form method="POST" action="{{ $form_action }}">
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
                                if(isset($request_order)){
                                    $client_name = explode(' - ', $request_order->request_o_client_name);
                                    $client_key = $request_order->request_o_client_id . '|' . $client_name[0];
                                }
                            @endphp
                            <option value="{{ $value['id'] . '|' . $value['name'] }}" {{ $value['id'] . '|' . $value['name'] == old('client_key', $client_key) ? 'selected' : '' }}>{{ $value['name'] . ' - ' . ($value['email'] ?? $value['id']) }}</option>
                        @endforeach
                    </select>
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
            <h6>請款單項目</h6>

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

                    <tbody>
                        @php
                            if($method == 'create'){
                                $items = [];
                            } else {
                                $items = json_decode($request_order->request_o_items) ?? [];
                            }
                        @endphp
                        @for ($i = 0; $i < 10; $i++)
                        <tr>
                            <td>{{ $i + 1 }}<input type="hidden" name="request_o_item_id[{{ $i }}]" value="{{ $items[$i]->id ?? '' }}"></td>

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
                                <input type="number" name="price[{{ $i }}]" 
                                    value="{{ old('price.' . $i, $items[$i]->price ?? '') }}" min="0" 
                                    class="d-target r-target form-control form-control-sm @error('price.' . $i) is-invalid @enderror" 
                                    aria-label="金額" placeholder="請輸入金額" disabled>
                            </td>

                            <td>
                                <input type="number" name="qty[{{ $i }}]" 
                                    value="{{ old('qty.' . $i, $items[$i]->qty ?? '') }}" min="0" 
                                    class="d-target r-target form-control form-control-sm @error('qty.' . $i) is-invalid @enderror" 
                                    aria-label="數量" placeholder="請輸入數量" disabled>
                            </td>

                            <td>
                                <input type="text" name="summary[{{ $i }}]" 
                                    class="d-target form-control form-control-sm @error('summary.' . $i) is-invalid @enderror" 
                                    value="{{ old('summary.' . $i, $items[$i]->summary ?? '') }}" 
                                    aria-label="摘要" placeholder="請輸入摘要" disabled>
                            </td>

                            <td>
                                <input type="text" name="memo[{{ $i }}]" 
                                    value="{{ old('memo.' . $i, $items[$i]->memo ?? '') }}" 
                                    class="d-target form-control form-control-sm @error('memo.' . $i) is-invalid @enderror" 
                                    aria-label="備註" placeholder="請輸入備註" disabled>
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