@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.category.index', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>
    <div class="card">
        <div class="card-header">
            @if ($method === 'create') 新增 @else 編輯 @endif 商品類別
        </div>
        <form class="card-body" method="post" action="{{ $formAction }}">
            @method('POST')
            @csrf
            <x-b-form-group name="category" title="商品類別" required="true">
                <input type="text"
                       class="form-control @error('name') is-invalid @enderror"
                       id="category"
                       name="category"
                       value="{{ old('category', $category ?? '')}}"
                         aria-label="商品類別" />
                @error('category')
                    <div class="alert-danger"> {{ $message }} </div>
                @enderror
            </x-b-form-group>
            @if ($method === 'edit')
                <input type='hidden' name='id' value="{{ old('id', $id) }}" />
            @endif
            <div class="d-flex justify-content-end pt-2">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
            </div>
        </form>
    </div>
@endsection
@once
    @push('sub-scripts')

    @endpush
@endonce
