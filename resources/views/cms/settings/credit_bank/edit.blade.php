@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.credit_bank.index', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>

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

                <x-b-form-group name="grade_id" title="會計科目 {{$data->grade_fk ?? ''}}" required="true">
                    <select name="grade_id" aria-label="會計科目"
                            class="form-select -select2 -single @error('grade_id') is-invalid @enderror">
                        <option value="" @if (true == empty(old('grade_id', ($data->grade_fk) ?? ''))) selected @endif >請選擇</option>
                        @foreach ($total_grades as $grade_value)
                            <option value="{{ $grade_value['primary_id'] }}"
                                    @if ($grade_value['primary_id'] == old('grade_id', ($data->grade_fk) ?? '')) selected @endif>
                                {{ $grade_value['code'] . ' ' . $grade_value['name'] }}
                            </option>
                        @endforeach
                    </select>
                </x-b-form-group>

                @if ($method === 'edit')
                    <input type='hidden' name='id' value="{{ old('id', $data->id) }}" />
                @endif
                @error('id')
                <div class="alert alert-danger mt-3">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
        </div>
    </form>
@endsection
@once
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endonce
