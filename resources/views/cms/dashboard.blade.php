@extends('layouts.main')
@section('sub-content')
<div class="d-flex align-items-center">
    <a href="/demo" class="btn btn-warning -in-header">Demo page</a>
    <a href="{{ Route('cms.combo-product.edit-combo', ['id' => 1]) }}" class="btn btn-info -in-header">組合包</a>
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
