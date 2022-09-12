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
                    <div class="col-form-label">{{ $data->bank_title ?? '' }}</div>
                </x-b-form-group>
                <x-b-form-group name="title" title="信用卡分期期數" required="true">
                    <div class="col-form-label">{{ $installment[$data->bank_installment] ?? '' }}</div>
                </x-b-form-group>
                <x-b-form-group name="title" title="卡別" required="true">
                    <div class="col-form-label">{{ $data->credit_title ?? '' }}</div>
                </x-b-form-group>
                <x-b-form-group name="title" title="會計科目代碼" required="true">
                    <div class="col-form-label">{{ $data->grade_code ?? '' }}</div>
                </x-b-form-group>
                <x-b-form-group name="title" title="會計科目" required="true">
                    <div class="col-form-label">{{ $data->grade_name ?? '' }}</div>
                </x-b-form-group>
                <x-b-form-group name="title" title="利率" required="true">
                    <input class="form-control @error('percent') is-invalid @enderror" name="percent"
                           value="{{ old('percent', $data->percent ?? '') }}" />
                </x-b-form-group>

                <input type='hidden' name='id' value="{{ old('id', $data->id) }}" />
                @error('percent')
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
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endonce
