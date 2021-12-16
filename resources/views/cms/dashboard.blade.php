@extends('layouts.main')
@section('sub-content')
<div class="d-flex align-items-center">
    <a href="/demo" class="btn btn-warning -in-header">Demo page</a>

    <a href="/cms/product" class="btn btn-primary -in-header">
        商品主頁
    </a>
    <a href="/cms/product/create" class="btn btn-outline-primary -in-header">
        <i class="bi bi-plus-circle"></i> 新增商品
    </a>
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
