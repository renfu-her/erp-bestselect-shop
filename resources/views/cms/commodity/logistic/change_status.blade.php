@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">配送狀態</h2>
    @error('error_msg')
    <div class="alert alert-danger" role="alert">
        {{ $message }}
    </div>
    @enderror
@endsection
@once
    @push('sub-scripts')
    @endpush
@endonce
