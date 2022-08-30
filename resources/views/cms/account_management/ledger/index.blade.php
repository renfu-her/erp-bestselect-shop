@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">分類帳查詢</h2>

    <form method="GET" action="{{ $form_action }}">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">會計科目 <span class="text-danger">*</span></label>
                    <select class="form-select -select2 -single" name="grade_id" data-placeholder="請選擇會計科目" required>
                        <option value="" selected disabled>請選擇</option>
                        @foreach($total_grades as $g_value)
                            <option value="{{ $g_value['primary_id'] }}"{{ $g_value['primary_id'] == old('grade_id') ? 'selected' : '' }}
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
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">傳票日期起訖</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('sdate') is-invalid @enderror" name="sdate" value="{{ old('sdate') }}" aria-label="傳票起始日期">
                        <input type="date" class="form-control -endDate @error('edate') is-invalid @enderror" name="edate" value="{{ old('edate') }}" aria-label="傳票結束日期">
                        <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                        <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                        <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                        <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                        <button class="btn" data-daysBefore="month" type="button">本月</button>
                        <div class="invalid-feedback">
                            @error('sdate')
                                {{ $message }}
                            @enderror
                            @error('edate')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">金額</label>
                    <div class="input-group has-validation">
                        <input type="number" step="1" min="0" class="form-control @error('min_price') is-invalid @enderror" 
                        name="min_price" value="{{ old('min_price') }}" placeholder="起始金額" aria-label="起始金額">
                        <input type="number" step="1" min="0" class="form-control @error('max_price') is-invalid @enderror" 
                        name="max_price" value="{{ old('max_price') }}" placeholder="結束金額" aria-label="結束金額">
                        <div class="invalid-feedback">
                            @error('min_price')
                                {{ $message }}
                            @enderror
                            @error('max_price')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>

            </div>

            <div class="col">
                <button type="submit" class="btn btn-primary px-4">搜尋</button>
            </div>
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
        </script>
    @endpush
@endOnce
