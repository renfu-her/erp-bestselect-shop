@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">
        新增手動紅利
    </h2>
    <form id="form1" method="post" action="{{ $formAction }}" enctype="multipart/form-data">
        @method('POST')
        @csrf

        <div class="card shadow p-4 mb-4">
            <div class="row">
                <x-b-form-group name="category" title="會計科目" required="true">
                    <select class="form-select -select" name="category" aria-label="會計科目"
                        data-placeholder="請選擇會計科目" required>
                        <option value="" selected disabled>請選擇</option>
                        @foreach ($dividendCategory as $key => $value)
                            <option value="{{ $key }}">{{ $value }} </option>
                        @endforeach
                    </select>
                </x-b-form-group>
                <div class="col-12 mb-3">
                    <label class="form-label">備註 <span class="text-danger">*</span></label>
                    <input class="form-control" name="note" type="text" placeholder="備註"
                        value="{{ old('note', '') }}" required aria-label="備註" required>
                </div>
                <div class="col-12">
                    <label class="form-label">匯入Excel（.xls, .xlsx）<span class="text-danger">*</span></label>
                    <fieldset class="col-12 mb-1">
                        <div class="px-1">
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="file_type" type="radio" checked>
                                    以會員編號匯入
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="file_type" type="radio">
                                    以會員E-mail匯入
                                </label>
                            </div>
                        </div>
                    </fieldset>
                    <div class="input-group has-validation">
                        <input type="file" class="form-control @error('file') is-invalid @enderror" name="file"
                            aria-label="匯入Excel" required 
                            accept="application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"/>
                        <a href="{{ Route('cms.manual-dividend.sample', null, true) }}" class="btn btn-success">
                            範本
                        </a>
                        <div class="invalid-feedback">
                            @error('file')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">上傳</button>
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