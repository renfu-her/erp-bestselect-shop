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

    <h2 class="mb-4">新增代墊單</h2>

    <form method="POST" action="{{ $form_action }}">
        @csrf
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">客戶 <span class="text-danger">*</span></label>
                        <select class="form-select -select2 -single" name="client_key" aria-label="客戶" data-placeholder="請選擇客戶" required>
                            <option value="" selected disabled>請選擇</option>
                            @foreach ($client as $value)
                                <option value="{{ $value['id'] . '|' . $value['name'] }}" {{ $value['id'] . '|' . $value['name'] == old('client_key') ? 'selected' : '' }}>{{ $value['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">會計科目 <span class="text-danger">*</span></label>
                        <select class="form-select -select2 -single @error('stitute_grade_id') is-invalid @enderror" name="stitute_grade_id" data-placeholder="請選擇會計科目" required>
                            <option value="" selected disabled>請選擇</option>
                            @foreach($total_grades as $g_value)
                                <option value="{{ $g_value['primary_id'] }}"{{ $g_value['primary_id'] == old('stitute_grade_id') ? 'selected' : '' }}
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

                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">金額（單價） <span class="text-danger">*</span></label>
                        <input type="number" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', 1) }}" min="0" placeholder="請輸入金額" data-placeholder="金額" required>
                        <div class="invalid-feedback">
                            @error('price')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">數量 <span class="text-danger">*</span></label>
                        <input type="number" name="qty" class="form-control @error('qty') is-invalid @enderror" value="{{ old('qty', 1) }}" min="0" placeholder="請輸入數量" data-placeholder="數量" required>
                        <div class="invalid-feedback">
                            @error('qty')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">摘要</label>
                        <input type="text" name="summary" class="form-control @error('summary') is-invalid @enderror" value="{{ old('summary') }}" placeholder="請輸入摘要" data-placeholder="摘要">
                        <div class="invalid-feedback">
                            @error('summary')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">備註</label>
                        <input type="text" name="memo" class="form-control @error('memo') is-invalid @enderror" value="{{ old('memo') }}" placeholder="請輸入備註" data-placeholder="備註">
                        <div class="invalid-feedback">
                            @error('memo')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>


        {{--
        <div class="px-0">
        </div>
        --}}
        <div class="col-auto">
            <a href="{{ Route('cms.stitute.index') }}" class="btn btn-outline-primary px-4" role="button">取消</a>
            <button type="submit" class="btn btn-primary px-4">確認</button>
        </div>
    </form>
@endsection

@once
    @push('sub-styles')
        
    @endpush
    @push('sub-scripts')
        <script>
            $(function() {
                // 會計科目樹狀排版
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
            });
        </script>
    @endpush
@endonce