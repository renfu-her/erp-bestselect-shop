@extends('layouts.main')
@section('sub-content')

    <form method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf
        <div class="card mb-4">
            <div class="card-header">
                @if ($method === 'create')
                    新增
                @else
                    編輯
                @endif
            </div>
            <div class="card-body">
                <x-b-form-group name="title" title="銀行名稱" required="true">
                    <input class="form-control @error('title') is-invalid @enderror" name="title"
                           value="{{ old('title', $data->title ?? '') }}" />
                </x-b-form-group>
                <x-b-form-group name="title" title="傳票代碼" required="false">
                    <input class="form-control @error('bank_code') is-invalid @enderror" name="bank_code"
                           value="{{ old('bank_code', $data->bank_code ?? '') }}" />
                </x-b-form-group>

                <x-b-form-group name="grade_id" title="會計科目" required="true">
                    <select name="grade_id" aria-label="會計科目"
                            class="form-select -select2 -single @error('grade_id') is-invalid @enderror">
                        <option value="" @if (true == empty(old('grade_id', ($data->grade_fk) ?? ''))) selected @endif >請選擇</option>
                        @foreach ($total_grades as $grade_value)
                            <option value="{{ $grade_value['primary_id'] }}" @if ($grade_value['primary_id'] == old('grade_id', ($data->grade_fk) ?? '')) selected @endif
                                    @if($grade_value['grade_num'] === 1)
                                        class="grade_1"
                                    @elseif($grade_value['grade_num'] === 2)
                                        class="grade_2"
                                    @elseif($grade_value['grade_num'] === 3)
                                        class="grade_3"
                                    @elseif($grade_value['grade_num'] === 4)
                                        class="grade_4"
                                    @endif
                                >
                                {{ $grade_value['code'] . ' ' . $grade_value['name'] }}
                            </option>
                        @endforeach
                    </select>
                </x-b-form-group>

                <div class="form-group">
                    <label class="col-form-label" for="installment">信用卡分期期數 <span class="text-danger">*</span></label>
                    <select name="installment" id="installment" class="-select2 form-select @error('installment') is-invalid @enderror" data-placeholder="請選擇信用卡分期期數" required hidden>
                        <option value="" {{ empty(old('grade_id', ($data->installment) ?? '')) ? 'selected' : '' }}>請選擇信用卡分期期數</option>
                        @foreach ($installment as $key => $value)
                        <option value="{{ $key }}" {{ old('installment', $data->installment ?? '') == $key ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                    @error('installment')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @if ($method === 'edit')
                    <input type='hidden' name='id' value="{{ old('id', $data->id) }}" />
                @endif
                @error('id')
                <div class="alert alert-danger mt-3">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
            <a href="{{ url()->previous() }}" class="btn btn-outline-primary px-4" role="button">
                返回上一頁
            </a>
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
        </style>
    @endpush

    @push('sub-scripts')
        <script>
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
