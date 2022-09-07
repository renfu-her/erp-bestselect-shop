@extends('layouts.layout')
@section('content')

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">{{ __('Reset Password') }}</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                            <div class="row mb-0">
                                <a href="{{ env('FRONTEND_URL') }}" target="_blank">返回喜鴻購物官網</a>
                            </div>
                        @else

                            <form method="POST" action="{{ route('customer.password.email') }}">
                                @csrf

                                <div class="row mb-3">
                                    <label for="email" class="col-md-3 col-form-label text-md-end">{{ __('Email Address') }}</label>

                                    <div class="col-md-8">
                                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                        @error('email')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4">
                                        <button type="submit" class="btn btn-primary">
                                            {{ __('Send Password Reset Link') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@once
    @push('styles')
        <link rel="stylesheet" href="{{ Asset('dist/css/sub-content.css') }}">
        <link rel="stylesheet" href="{{ Asset('dist/css/component.css') }}">
        @stack('sub-styles')
    @endpush
    @push('scripts')
        <script src="{{ Asset('dist/js/dashboard.js') }}"></script>
        <script src="{{ Asset('dist/js/helpers.js') }}"></script>
        <script src="{{ Asset('dist/js/components.js') }}"></script>
        <script>
            window.Laravel = {!! json_encode([
                'apiToken' => auth()->user()->api_token ?? null,
                'apiUrl' => [
                    'getRegions' => Route('api.addr.get-regions'),
                    'addrFormating' => Route('api.addr.formating'),
                    'productStyles' => Route('api.cms.product.get-product-styles'),
                    'productList'=>Route('api.cms.product.get-products'),
                    'productShipments'=>Route('api.cms.product.get-products-shipment')
                ],
            ]) !!};

            window.axios.defaults.headers.common['Authorization'] = 'Bearer ' + Laravel.apiToken;
            window.axios.defaults.headers.common['Accept'] = 'application/json';
        </script>
        @stack('sub-scripts')
    @endpush
@endonce
