@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">信用卡作業管理</h2>

    <fieldset class="col-12 mb-2">
        <div class="p-2 border rounded">
            <a href="{{ Route('cms.credit_percent.index') }}" class="btn btn-success" role="button">請款比例列表</a>
            <a href="{{ Route('cms.credit_bank.index') }}" class="btn btn-primary" role="button">銀行列表</a>
            <a href="{{ Route('cms.credit_card.index') }}" class="btn btn-danger" role="button">信用卡列表</a>
        </div>
    </fieldset>
    <div class="card shadow p-4 mb-4">
        <div class="col mb-4">
        </div>
    </div>
@endsection

@once
    @push('sub-scripts')
    @endpush
@endonce
