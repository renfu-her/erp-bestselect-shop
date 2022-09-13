@extends('layouts.main')
@section('sub-content')
    <form method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf
        <div class="card mb-4">
            <div class="card-header">
                @if ($method === 'create')
                    新增
                @else
                    編輯
                @endif
            </div>
            <div class="card-body">
                <x-b-form-group name="title" title="名稱" required="true">
                    <input class="form-control @error('title') is-invalid @enderror" name="title"
                           value="{{ old('title', $data->title ?? '') }}" />
                </x-b-form-group>

                @if ($method === 'edit')
                    <input type='hidden' name='id' value="{{ old('id', $data->id) }}" />
                @endif
                @error('id')
                <div class="alert alert-danger mt-3">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
            <a href="{{ url()->previous() }}" class="btn btn-outline-primary px-4" role="button">
                返回上一頁
            </a>
        </div>
    </form>
@endsection

@once
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endonce
