@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">寄倉訂購</h2>

    <div class="col">
        @can('cms.consignment.create')
            <a href="{{ Route('cms.consignment.order', null, true) }}" class="btn btn-primary">
                <i class="bi bi-plus-lg pe-1"></i> 新增寄倉訂購單
            </a>
        @endcan
    </div>
@endsection
@once
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endonce
