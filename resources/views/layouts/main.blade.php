@extends('layouts.layout')
@section('content')
    <!-- Header -->
    <x-b-topbar />
    <div class="container-fluid">
        <div class="row flex-nowrap position-relative">
            <!-- 左側 Menu -->
            <x-b-sidebar />

            <div id="Main" class="col p-0">
                <!-- 麵包屑 -->
                <x-b-breadcrumb :value="isset($breadcrumb_data) ? $breadcrumb_data : ''" />


                <!-- 主內容 -->
                <main class="px-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="px-4 py-3">
                            @yield('sub-content')
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>
    <x-b-toast />
@endsection


@once
    @push('styles')
        <link rel="stylesheet" href="{{ Asset('dist/css/sub-content.css') }}?1.1">
        <link rel="stylesheet" href="{{ Asset('dist/css/component.css') }}?1.0">
        @stack('sub-styles')
    @endpush
    @push('scripts')
        <script src="{{ Asset('dist/js/dashboard.js') }}?1.2"></script>
        <script src="{{ Asset('dist/js/helpers.js') }}?1.0"></script>
        <script src="{{ Asset('dist/js/components.js') }}?2.1"></script>
        <script>
            window.Laravel = {!! json_encode([
                'apiToken' => auth()->user()->api_token ?? null,
                'apiUrl' => [
                    'getRegions' => Route('api.addr.get-regions'),
                    'addrFormating' => Route('api.addr.formating'),
                    'productStyles' => Route('api.cms.product.get-product-styles'),
                    'productList' => Route('api.cms.product.get-products'),
                    'productShipments' => Route('api.cms.product.get-products-shipment'),
                    'inboundList' => Route('api.cms.delivery.get-select-inbound'),
                    'selectProductList' => Route('api.cms.depot.get-select-product'),
                    'selectCsnProductList' => Route('api.cms.depot.get-select-csn-product'),
                ],
            ]) !!};

            window.axios.defaults.headers.common['Authorization'] = 'Bearer ' + Laravel.apiToken;
            window.axios.defaults.headers.common['Accept'] = 'application/json';
        </script>
        @stack('sub-scripts')
    @endpush
@endonce
