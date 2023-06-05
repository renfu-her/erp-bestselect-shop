@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">
        新增手動紅利

    </h2>
    <form id="form1" class="card-body" method="post" action="{{ $formAction }}" enctype="multipart/form-data">
        @method('POST')
        @csrf

        <div class="card shadow p-4 mb-4">
            <div class="row">
                <x-b-form-group name="category" title="會計科目" required="true" class="mb-2">
                    <select class="form-select -select" name="category" aria-label="會計科目"
                        data-placeholder="請選擇會計科目">
                        @foreach ($dividendCategory as $key => $value)
                            <option value="{{ $key }}">{{ $value }} </option>
                        @endforeach
                    </select>
                </x-b-form-group>
                <div class="d-flex align-items-center mb-4">
                    <input type="file" class="form-control @error('file') is-invalid @enderror" name="file" required
                        aria-label="excel" />
                </div>
            </div>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
            <a href="{{ Route('cms.manual-dividend.index', [], true) }}" class="btn btn-outline-primary px-4"
                role="button">返回列表</a>
        </div>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </form>

@endsection
@once
    @push('sub-scripts')
    @endpush
@endonce
