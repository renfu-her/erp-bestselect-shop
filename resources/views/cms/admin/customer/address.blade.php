@extends('layouts.main')
@section('sub-content')
    <div>
        <x-b-customer-navi :customer="$customer"></x-b-customer-navi>
    </div>
    <div class="card shadow p-4 mb-4">
        <h6>預設地址</h6>
        <div class="col-form-label">{{ $defaultAddress->address ?? '' }}</div>

        @if (!is_null($otherAddress))
            <h6>其它收件地址</h6>
            @foreach($otherAddress as $data)
                <div class="form-group">
                    <div class="input-group">
                        {{ $data->address ?? ''}}
                    </div>
                </div>
            @endforeach
        @endif
    </div>
@endsection
