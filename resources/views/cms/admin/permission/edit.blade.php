@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.permission.index', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>
    <div class="card">
        <div class="card-header">
            @if ($method === 'create') 新增 @else 編輯 @endif 頁面
        </div>
        <form class="card-body" method="post" action="{{ $formAction }}">
            @method('POST')
            @csrf
            <x-b-form-group name="title" title="頁面名稱" required="true">
                <input class="form-control @error('title') is-invalid @enderror" name="title"
                       value="{{ old('title', $data->title ?? '') }}"/>
            </x-b-form-group>
            <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
            </div>
        </form>
    </div>

@endsection
@once
    @push('sub-scripts')

    @endpush
@endonce
