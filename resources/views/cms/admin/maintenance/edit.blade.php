@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ Route("cms.dashboard", [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回首頁
        </a>
    </div>

    <form method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf

        <div class="card mb-4">
            <div class="card-header">
                資料維護
            </div>
            <div class="card-body">
                <x-b-form-group name="name" title="姓名">
                    <div class="form-control" readonly>{{ $data->name }}</div>
                </x-b-form-group>
                <x-b-form-group name="password" title="密碼">
                    <input class="form-control @error('password') is-invalid @enderror" type="password"
                    name="password" value="" />
                </x-b-form-group>
                <x-b-form-group name="password_confirmation" title="密碼確認">
                    <input class="form-control @error('password_confirmation') is-invalid @enderror" type="password"
                        name="password_confirmation" value="" />
                </x-b-form-group>

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-primary px-4">儲存</button>
                </div>
            </div>
        </div>
    </form>

@endsection
@once
    @push('sub-scripts')
 
    @endpush
@endonce
