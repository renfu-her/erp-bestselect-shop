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

    <h2 class="mb-4">信用卡刷卡記錄</h2>
    <a href="{{ route('cms.credit_manager.record', ['id'=>$record->credit_card_received_id]) }}" class="btn btn-primary" role="button">
        <i class="bi bi-arrow-left"></i> 返回上一頁
    </a>
    <form method="POST" action="{{ $form_action }}">
        @csrf
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-sm-4 mb-3">
                        <label class="form-label">信用卡卡號</label>
                        <input type="text" name="credit_card_number" class="form-control @error('credit_card_number') is-invalid @enderror" value="{{ $record->credit_card_number }}" placeholder="請輸入信用卡卡號" data-placeholder="信用卡卡號">
                        <div class="invalid-feedback">
                            @error('credit_card_number')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-sm-4 mb-3">
                        <label class="form-label">授權碼</label>
                        <input type="text" name="credit_card_authcode" class="form-control @error('credit_card_authcode') is-invalid @enderror" value="{{ $record->credit_card_authcode }}" placeholder="請輸入授權碼" data-placeholder="授權碼">
                        <div class="invalid-feedback">
                            @error('credit_card_authcode')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-sm-4 mb-3">
                        <label class="form-label">信用卡別</label>
                        <select class="form-select -select2 -single @error('credit_card_type_code') is-invalid @enderror" name="credit_card_type_code" data-placeholder="請選擇信用卡別">
                            <option value="">請選擇</option>
                            @foreach($card_type as $key => $value)
                                <option value="{{ $key }}"{{ $key == $record->credit_card_type_code ? 'selected' : ''}}>{{ $value }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">
                            @error('credit_card_type_code')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-sm-4 mb-3">
                        <label class="form-label">持卡人</label>
                        <input type="text" class="form-control @error('credit_card_owner_name') is-invalid @enderror" name="credit_card_owner_name" value="{{ $record->credit_card_owner_name }}" placeholder="請輸入持卡人" data-placeholder="持卡人">
                        <div class="invalid-feedback">
                            @error('credit_card_owner_name')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-sm-4 mb-3">
                        <label class="form-label">刷卡日期</label>
                        <input type="date" class="form-control @error('credit_card_checkout_date') is-invalid @enderror" name="credit_card_checkout_date" value="{{ date('Y-m-d', strtotime($record->credit_card_checkout_date)) ?? date('Y-m-d', strtotime( date('Y-m-d'))) }}" placeholder="請輸入刷卡日期" data-placeholder="刷卡日期">
                        <div class="invalid-feedback">
                            @error('credit_card_checkout_date')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-sm-4 mb-3">
                        <label class="form-label">會計科目 <span class="text-danger">*</span></label>
                        <select class="form-select -select2 -single @error('received_grade_id') is-invalid @enderror" name="received_grade_id" data-placeholder="請選擇會計科目" required>
                            <option value="" selected disabled>請選擇</option>
                            @foreach($total_grades as $g_value)
                                <option value="{{ $g_value['primary_id'] }}"{{ $g_value['primary_id'] == $record->ro_received_grade_id ? 'selected' : '' }}
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
                </div>

                <div class="row">
                    <fieldset class="col-12 col-sm-4 mb-3">
                        <legend class="col-form-label p-0 mb-2">線上刷卡 <span class="text-danger">*</span></legend>
                        <div class="px-1 pt-1">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" name="credit_card_checkout_mode" value="online" type="radio" required {{ $record->credit_card_checkout_mode == 'online' ? 'checked' : '' }}>
                                <label class="form-check-label" for="now">是</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" name="credit_card_checkout_mode" value="offline" type="radio" required {{ $record->credit_card_checkout_mode == 'offline' ? 'checked' : '' }}>
                                <label class="form-check-label" for="postpone">否</label>
                            </div>
                        </div>
                        <div class="invalid-feedback">
                            @error('credit_card_checkout_mode')
                            {{ $message }}
                            @enderror
                        </div>
                    </fieldset>

                    <div class="col-12 col-sm-4 mb-3">
                        <label class="form-label">刷卡地區</label>
                        <select class="form-select -select2 -single @error('credit_card_area_code') is-invalid @enderror" name="credit_card_area_code" data-placeholder="請選擇信用卡別">
                            <option value="">請選擇</option>
                            @foreach($checkout_area as $key => $value)
                                <option value="{{ $key }}"{{ $key == $record->credit_card_area_code ? 'selected' : ''}}>{{ $value }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">
                            @error('credit_card_area_code')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-sm-4 mb-3">
                        <label class="form-label">備註</label>
                        <input type="text" class="form-control @error('note') is-invalid @enderror" name="note" value="{{ $record->note }}">
                        @error('note')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="px-0">
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
