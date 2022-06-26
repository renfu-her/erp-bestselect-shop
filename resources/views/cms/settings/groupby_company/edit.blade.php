@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.groupby-company.index', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>

    <form method="post" action="">
        @method('POST')
        @csrf

        <div class="card mb-4">
            <div class="card-header">
                @if ($method === 'create') 新增 @else 編輯 @endif 團購主公司
            </div>
            <div class="card-body">
                <div class="row">
                    <x-b-form-group name="name" title="公司名稱" required="true" class="col-12 col-sm-6">
                        <input class="form-control @error('name') is-invalid @enderror" name="name" value="" />
                    </x-b-form-group>
                    <x-b-form-group name="name" title="啟用" required="true" class="col-12 col-sm-6">
                        <div class="form-check form-switch form-switch-lg">
                            <input class="form-check-input" type="checkbox" name="active" value="1" checked>
                        </div>
                    </x-b-form-group>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                新增子團
            </div>
            <div class="card-body">
                <div class="row">
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
        </div>
    </form>
@endsection
@once
    @push('sub-styles')
    <style>
        label.form-check-label[data-type]::after{
            content: attr(data-type)
        }
    </style>
    @endpush
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endonce
