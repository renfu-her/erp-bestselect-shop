@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">#{{ $breadcrumb_data['sn'] }} 銷貨退回明細</h2>
    @if ($event === 'consignment')
        <x-b-consign-navi :id="$delivery->event_id"></x-b-consign-navi>
    @endif
    @if ($event === 'csn_order')
        <x-b-csnorder-navi :id="$delivery->event_id"></x-b-csnorder-navi>
    @endif
    @error('error_msg')
    <div class="alert alert-danger" role="alert">
        {{ $message }}
    </div>
    @enderror
    @error('item_error')
    <div class="alert alert-danger" role="alert">
        {{ $message }}
    </div>
    @enderror
    @if($errors->any())
        {!! implode('', $errors->all('<div>:message</div>')) !!}
    @endif

@endsection
@once
    @push('sub-scripts')
        <script>
        $(function () {
        });
        </script>
    @endpush
@endonce
