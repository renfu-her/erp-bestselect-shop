@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.customer.index', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>

    <form method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf

        <div class="card mb-4">
            <div class="card-header">
                @if ($method === 'create') 新增 @else 編輯 @endif 帳號
            </div>
            <div class="card-body">
                <x-b-form-group name="name" title="姓名" required="true">
                    <input class="form-control @error('name') is-invalid @enderror" name="name"
                           value="{{ old('name', $data->name ?? '') }}"/>
                </x-b-form-group>
                <x-b-form-group name="account" title="帳號" required="true">
                    @if ($method === 'create')
                        <input class="form-control @error('email') is-invalid @enderror" name="email"
                               value="{{ old('email', $data->email ?? '') }}"/>
                    @else
                        <div class="col-form-label">{{ $data->email ?? '' }}</div>
                    @endif
                </x-b-form-group>
                <x-b-form-group name="password" title="密碼" required="{{ ($method === 'create') ? 'true' : 'false' }}">
                    <input class="form-control @error('password') is-invalid @enderror" type="password"
                           name="password" value=""/>
                </x-b-form-group>
                <x-b-form-group name="password_confirmation" title="密碼確認">
                    <input class="form-control @error('password_confirmation') is-invalid @enderror" type="password"
                           name="password_confirmation" value=""/>
                </x-b-form-group>

                @if ($method === 'edit')
                    <input type='hidden' name='id' value="{{ old('id', $id) }}"/>
                @endif
                @error('id')
                <div class="alert alert-danger mt-3">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
        </div>
    </form>

@endsection
@once
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endonce
