@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ $backUrl }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>
    <div class="card shadow p-4 mb-4">
        <h6>匯款明細</h6>
        <dl class="row">
            <div class="col">
                <dt>類型</dt>
                <dd>{{ $data->type }}</dd>
            </div>
        </dl>
        <dl class="row">
            <div class="col">
                <dt>匯款號</dt>
                <dd>{{ $data->sn ?? '' }}</dd>
            </div>
        </dl>
        <dl class="row">
            <div class="col">
                <dt>匯款日期</dt>
                <dd>{{ $data->remit_date ?? '' }}</dd>
            </div>
        </dl>
        <dl class="row">
            <div class="col">
                <dt>會計科目</dt>
                <dd>{{ $data->code ?? '' }}</dd>
            </div>
        </dl>
        <dl class="row">
            <div class="col">
                <dt>金額</dt>
                <dd>{{ $data->tw_price ?? '' }}</dd>
            </div>
        </dl>
        <dl class="row">
            <div class="col">
                <dt>公司</dt>
                <dd>{{ $data->name ?? '' }}</dd>
            </div>
        </dl>
        <dl class="row">
            <div class="col">
                <dt>備註</dt>
                <dd>{{ $data->memo ?? '' }}</dd>
            </div>
        </dl>
    </div>
@endsection
@once
    @push('sub-scripts')

    @endpush
@endonce
