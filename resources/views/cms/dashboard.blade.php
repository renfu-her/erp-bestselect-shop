@extends('layouts.main')
@section('sub-content')
@if ($_SERVER['SERVER_NAME'] === '127.0.0.1' || $_SERVER['SERVER_NAME'] === 'localhost')
    <a href="/demo" class="btn btn-warning">Demo page</a>
@endif

<div class="d-flex flex-wrap">
    <div class="col-12 col-lg-9">
        {{-- 電商訂單 --}}
        <div class="d-flex flex-column flex-md-row mb-3 border">
            <div class="col border-end">NT$123456 / 78筆訂單</div>
            <div class="col border-end">NT$123456 / 78筆訂單</div>
            <div class="col">NT$123456 / 78筆訂單</div>
        </div>

        {{-- 電商流量 --}}
        <div class="d-flex flex-column flex-md-row mb-3 border">
            <div class="col border-end">135790</div>
            <div class="col border-end">135790</div>
            <div class="col">135790</div>
        </div>

        {{-- 推薦商品 --}}
        <div class="row g-3 mb-3">
            <div class="col-6 col-md-3">
                <div class="p-3 border">群組1</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 border">群組2</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 border">群組3</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 border">群組4</div>
            </div>
        </div>

        <div class="border">通知事項</div>
        <div class="border">公告事項</div>
    </div>

    <div class="col">
        <div class="border">排行榜</div>
    </div>
</div>
@endsection
@once
    @push('sub-styles')
    <style>
    </style>
    @endpush
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endOnce
