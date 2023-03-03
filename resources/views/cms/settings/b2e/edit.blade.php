@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">
        @if ($method === 'create')
            新增
        @else
            編輯
        @endif 企業網
    </h2>

    <form method="post" action="" enctype="multipart/form-data">
        @method('POST')
        @csrf

        <div class="card shadow p-4 mb-4">
            <div class="row">
                <x-b-form-group name="title" title="企業名稱" required="true" class="col-12 col-sm-6">
                    <input class="form-control @error('title') is-invalid @enderror" name="title"
                        value="{{ old('title', $data->title ?? '') }}" required />
                </x-b-form-group>
                <x-b-form-group name="short_title" title="企業簡稱" required="true" class="col-12 col-sm-6">
                    <input class="form-control @error('short_title') is-invalid @enderror" name="short_title"
                        value="{{ old('short_title', $data->short_title ?? '') }}" required />
                </x-b-form-group>
                <x-b-form-group name="code" title="驗證碼" required="true" class="col-12 col-sm-6">
                    <input class="form-control @error('code') is-invalid @enderror" name="code"
                        value="{{ old('code', $data->code ?? '') }}" required />
                </x-b-form-group>
                <x-b-form-group name="vat_no" title="統一編號" required="true" class="col-12 col-sm-6">
                    <input class="form-control @error('vat_no') is-invalid @enderror" name="vat_no"
                        value="{{ old('vat_no', $data->vat_no ?? '') }}" required />
                </x-b-form-group>
                <x-b-form-group name="tel" title="企業電話" required="false" class="col-12 col-sm-6">
                    <input class="form-control @error('tel') is-invalid @enderror" name="tel"
                        value="{{ old('tel', $data->tel ?? '') }}" />
                </x-b-form-group>
                <x-b-form-group name="ext" title="分機號碼" required="false" class="col-12 col-sm-6">
                    <input class="form-control @error('ext') is-invalid @enderror" name="ext"
                        value="{{ old('ext', $data->ext ?? '') }}" />
                </x-b-form-group>
                <x-b-form-group name="contact_person" title="窗口" required="true" class="col-12 col-sm-6">
                    <input class="form-control @error('contact_person') is-invalid @enderror" name="contact_person"
                        value="{{ old('contact_person', $data->contact_person ?? '') }}" required />
                </x-b-form-group>
                <x-b-form-group name="contact_tel" title="窗口手機" required="false" class="col-12 col-sm-6">
                    <input class="form-control @error('contact_tel') is-invalid @enderror" name="contact_tel"
                        value="{{ old('contact_tel', $data->contact_tel ?? '') }}" />
                </x-b-form-group>
                <x-b-form-group name="contact_email" title="窗口信箱" required="false" class="col-12 col-sm-6">
                    <input class="form-control @error('contact_email') is-invalid @enderror" name="contact_email"
                        value="{{ old('contact_email', $data->contact_email ?? '') }}" />
                </x-b-form-group>
                <x-b-form-group name="email" title="合約起訖日" required="true" class="col-12">
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('contract_sdate') is-invalid @enderror"
                            name="contract_sdate" value="{{ old('contract_sdate', $data->contract_sdate ?? '') }}"
                            aria-label="合約起始" required />
                        <input type="date" class="form-control -endDate @error('contract_edate') is-invalid @enderror"
                            name="contract_edate" value="{{ old('contract_edate', $data->contract_edate ?? '') }}"
                            aria-label="合約結束" required />
                        <div class="invalid-feedback">
                            @error('contract_sdate')
                                {{ $message }}
                            @enderror
                            @error('contract_edate')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </x-b-form-group>
                <x-b-form-group name="salechannel_id" title="銷售通路" required="true" class="col-12 col-sm-6">
                    <select class="form-select" name="salechannel_id">
                        @foreach ($salechannels as $value)
                            <option value="{{ $value->id }}" @if ($value->id == old('salechannel_id', $data->salechannel_id ?? '')) selected @endif>
                                {{ $value->title }}</option>
                        @endforeach
                    </select>
                </x-b-form-group>
                <x-b-form-group name="user_id" title="業務員" required="false" class="col-12 col-sm-6">
                    <select class="form-select -select2 -single" name="user_id">
                        <option value="">無</option>
                        @foreach ($users as $value)
                            <option value="{{ $value->id }}" @if ($value->id == old('user_id', $data->user_id ?? '')) selected @endif>
                                {{ $value->name }}</option>
                        @endforeach
                    </select>
                </x-b-form-group>
                <x-b-form-group name="img" title="LOGO" required="false" class="col-12 col-sm-6">
                    <input class="form-control @error('file') is-invalid @enderror" type="file" name="img"
                        accept=".jpg,.jpeg,.png,.gif">
                </x-b-form-group>
                @if (true == isset($data->img))
                    <x-b-form-group title="LOGO預覽" required="false" class="col-12 col-sm-6">
                        <div>
                            <img style="max-width:100%" src="{{ asset($data->img) }}" />
                        </div>
                    </x-b-form-group>
                @endif
            </div>
            @if ($method === 'edit')
                <input type='hidden' name='id' value="" />
            @endif
            @error('id')
                <div class="alert alert-danger mt-3">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
            <a href="{{ Route('cms.b2e-company.index', [], true) }}" class="btn btn-outline-primary" role="button">
                返回上一頁
            </a>
        </div>
    </form>
@endsection
@once
    @push('sub-styles')
        <style>
        </style>
    @endpush
    @push('sub-scripts')
        <script></script>
    @endpush
@endonce
