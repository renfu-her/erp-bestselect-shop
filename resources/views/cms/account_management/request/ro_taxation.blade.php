@extends('layouts.main')

@section('sub-content')
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

    <div class="pt-2 mb-3">
        <a href="{{ url()->previous() }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>

    <form method="POST" action="{{ $form_action }}">
        @csrf
        <div class="card shadow p-4 mb-4">
            <div class="table-responsive tableOverBox">
                <table class="table table-hover tableList mb-1">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center">編號</th>
                            <th scope="col">會計科目<span class="text-danger">*</span></th>
                            <th scope="col">摘要說明</th>
                            <th scope="col">數量</th>
                            <th scope="col">金額</th>
                            <th scope="col">稅別</th>
                            <th scope="col">備註</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php
                            $serial = 1;
                        @endphp

                        @foreach($received_data as $value)
                            <tr class="bg-info">
                                <td class="text-center">{{ $serial }}</td>
                                <td>
                                    <select class="form-select -select2 -single" name="received[{{ $value->received_id }}][grade_id]" data-placeholder="請選擇會計科目" required>
                                        <option value="" selected disabled>請選擇</option>
                                        @foreach($total_grades as $g_value)
                                            <option value="{{ $g_value['primary_id'] }}"{{ $g_value['primary_id'] == $value->all_grades_id ? 'selected' : '' }}
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

                                <td><input class="form-control" name="received[{{ $value->received_id }}][summary]" type="text" value="{{ $value->summary }}"></td>
                                <td>1</td>
                                <td>{{ number_format($value->tw_price, 2) }}</td>
                                <td>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" name="received[{{ $value->received_id }}][taxation]" value="1" type="radio" id="tax_{{ $serial }}_1" required @if ($value->taxation == '1') checked @endif>
                                        <label class="form-check-label" for="tax_{{ $serial }}_1">應稅</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" name="received[{{ $value->received_id }}][taxation]" value="0" type="radio" id="tax_{{ $serial }}_2" required @if ($value->taxation == '0') checked @endif>
                                        <label class="form-check-label" for="tax_{{ $serial }}_2">免稅</label>
                                    </div>
                                </td>
                                <td><input class="form-control" name="received[{{ $value->received_id }}][note]" type="text" value="{{ $value->note }}"></td>
                            </tr>
                            @php
                                $serial++;
                            @endphp
                        @endforeach

                        @foreach($order_list_data as $value)
                            <tr>
                                <td class="text-center">{{ $serial }}</td>
                                <td>
                                    <select class="form-select -select2 -single" name="product[{{ $value->id }}][request_grade_id]" data-placeholder="請選擇會計科目" required>
                                        <option value="" selected disabled>請選擇</option>
                                        @foreach($total_grades as $g_value)
                                            <option value="{{ $g_value['primary_id'] }}"{{ $g_value['primary_id'] == $value->request_grade_id ? 'selected' : '' }}
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
                                    {{-- $value->ro_received_grade_code . ' ' . $value->ro_received_grade_name --}}
                                    <input class="form-control" name="product[{{ $value->id }}][summary]" type="text" value="{{ $value->summary }}">
                                </td>
                                <td>1</td>
                                <td>{{ number_format($value->total_price, 2) }}</td>
                                <td>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" name="product[{{ $value->id }}][taxation]" value="1" type="radio" id="tax_{{ $serial }}_1" required @if ($value->taxation == '1') checked @endif>
                                        <label class="form-check-label" for="tax_{{ $serial }}_1">應稅</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" name="product[{{ $value->id }}][taxation]" value="0" type="radio" id="tax_{{ $serial }}_2" required @if ($value->taxation == '0') checked @endif>
                                        <label class="form-check-label" for="tax_{{ $serial }}_2">免稅</label>
                                    </div>
                                </td>
                                <td><input class="form-control" name="product[{{ $value->id }}][memo]" type="text" value="{{ $value->memo }}"></td>
                            </tr>
                            @php
                                $serial++;
                            @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">確認</button>
            {{-- <a onclick="history.back()" class="btn btn-outline-primary px-4" role="button">取消</a> --}}
        </div>
    </form>
@endsection

@once
@push('sub-scripts')
<script>
    // 會計科目樹狀排版
    $('.-select2').select2({
        templateResult: function (data) {
            // We only really care if there is an element to pull classes from
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
</script>
@endpush
@endonce