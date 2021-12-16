@extends('layouts.main')
@section('sub-content')
<div>
    <h2 class="mb-3">{{ $data->title }}</h2>
    <x-b-prd-navi id="{{  $data->id }}"></x-b-prd-navi>
</div>
<form action="">
    <div class="card shadow p-4 mb-4">
        <h6>設定</h6>
    </div>
</form>
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
