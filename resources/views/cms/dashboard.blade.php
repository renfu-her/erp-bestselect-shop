@extends('layouts.main')
@section('sub-content')
<div class="d-flex align-items-center">
    <a href="/demo" class="btn btn-warning -in-header">Demo page</a>

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
