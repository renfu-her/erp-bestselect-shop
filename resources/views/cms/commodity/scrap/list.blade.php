@extends('layouts.main')
@section('sub-content')

    <h2 class="mb-4">報廢管理</h2>

    <form id="search" action="{{ Route('cms.scrap.index') }}" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-md-6 mb-3">
                    <label class="form-label">報廢單號</label>
                    <input class="form-control" name="purchase_sn" type="text" placeholder="採購單號" value=""
                           aria-label="報廢單號">
                </div>
            </div>

            <div class="col">
                <button type="submit" class="btn btn-primary px-4">搜尋</button>
            </div>
        </div>
    </form>
    <form id="actionForms">
        @csrf
        <div class="card shadow p-4 mb-4">
            <div class="row justify-content-end mb-4">
                <div class="col">
                    @can('cms.scrap.create')
                        <a href="{{ Route('cms.scrap.create', null, true) }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg pe-1"></i> 新增報廢單
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </form>

@endsection

@once
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endonce
