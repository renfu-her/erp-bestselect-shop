@extends('layouts.layout')
@section('content')
    <!-- Header -->
    <x-b-topbar />
    <div class="container-fluid">
        <div class="row">
            <!-- 左側 Menu -->
            <x-b-sidebar />

            <!-- 麵包屑 -->
            <x-b-breadcrumb :value="isset($breadcrumb_data) ? $breadcrumb_data : ''" />


            <!-- 主內容 -->
            <main class="ms-sm-auto px-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="px-4 py-3">
                        @yield('sub-content')
                    </div>
                </div>
            </main>
        </div>
    </div>
    <x-b-toast />
@endsection


@once
    @push('styles')
        <link rel="stylesheet" href="{{ Asset('dist/css/sub-content.css') }}">
        @stack('sub-styles')
    @endpush
    @push('scripts')
        <script src="{{ Asset('dist/js/dashboard.js') }}"></script>
        <script src="{{ Asset('dist/js/helpers.js') }}"></script>
        <script>
            // window.axios.defaults.headers.common['Authorization'] = 'Bearer ' + Laravel.apiToken;
            window.axios.defaults.headers.common['Accept'] = 'application/json';
        </script>
        @stack('sub-scripts')
    @endpush
@endonce
