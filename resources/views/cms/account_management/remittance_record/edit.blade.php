@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">匯款明細</h2>

    <div class="card shadow p-4 mb-4">
        <dl class="row">
            <div class="col">
                <dt>類型</dt>
                <dd>{{ $data->type }}</dd>
            </div>
            
            <div class="col">
                <dt>匯款號</dt>
                <dd>{{ $data->sn ?? '' }}</dd>
            </div>
        </dl>
        <dl class="row">
            <div class="col">
                <dt>匯款日期</dt>
                <dd>{{ $data->remit_date ? date('Y/m/d', strtotime($data->remit_date)) : '' }}</dd>
            </div>
            
            <div class="col">
                <dt>會計科目</dt>
                <dd>{{ $data->code ?? '' }}</dd>
            </div>
        </dl>
        <dl class="row">
            <div class="col">
                <dt>金額</dt>
                <dd>{{ $data->tw_price ? '$'.$data->tw_price : '' }}</dd>
            </div>
            
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

    <div class="col-auto">
        <a href="{{ $backUrl }}" 
            class="btn btn-outline-primary px-4" role="button">
            返回列表
        </a>
    </div>
@endsection
@once
    @push('sub-scripts')

    @endpush
@endonce
