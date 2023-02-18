@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.erp-travel.index', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>
    <div class="card">
        <div class="card-header">
            新增規則
        </div>
        <form class="card-body" method="post" action="{{ $formAction }}">
            @method('POST')
            @csrf
            <x-b-form-group name="login_name" title="login_name" required="true">
                <input class="form-control @error('login_name') is-invalid @enderror" name="login_name"
                    value="{{ old('login_name', $data['login_name'] ?? '') }}"
                    @if ($method == 'edit') disabled @endif />
                @if ($method == 'edit')
                    <input type="hidden" name="login_name" value="{{ $data['login_name'] }}">
                @endif
            </x-b-form-group>
            <x-b-form-group name="login_pw" title="login_pw" required="true">
                <input class="form-control @error('login_pw') is-invalid @enderror" name="login_pw"
                    value="{{ old('login_pw', $data['login_pw'] ?? '') }}" required />
            </x-b-form-group>
            <x-b-form-group name="login_ip" title="login_ip">
                <input class="form-control @error('login_ip') is-invalid @enderror" name="login_ip"
                    value="{{ old('login_ip', $data['login_ip'] ?? '') }}" />
            </x-b-form-group>
            <x-b-form-group name="cf" title="cf">
                <input class="form-control @error('cf') is-invalid @enderror" name="cf"
                    value="{{ old('cf', $data['cf'] ?? '') }}" />
            </x-b-form-group>
            <x-b-form-group name="ittms_code" title="ittms_code">
                <input class="form-control @error('ittms_code') is-invalid @enderror" name="ittms_code"
                    value="{{ old('ittms_code', $data['ittms_code'] ?? '') }}" />
            </x-b-form-group>
            @php
                $column = ['status', 'agt_flag','flag_package','flag_ship','flag_tax','sales_type'];
            @endphp
            @foreach ($column as $cc)
                <x-b-form-group name="{{ $cc }}" title="{{ $cc }}">
                    <div>
                        @foreach ($radioOptions as $key => $value)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="{{ $cc }}" id="{{ $cc }}{{ $key }}"
                                    value="{{ $key }}" @if (old($cc, $data[$cc] ?? '') == $key) checked @endif>
                                <label class="form-check-label"
                                    for="{{ $cc }}{{ $key }}">{{ $value }}</label>
                            </div>
                        @endforeach
                    </div>
                </x-b-form-group>
            @endforeach




            {{--            @if ($method === 'edit') --}}
            {{--                <input type='hidden' name='id' value="{{ old('id', $id) }}" /> --}}
            {{--            @endif --}}
            {{--            @error('title') --}}
            {{--                <div class="alert alert-danger mt-3">{{ $message }}</div> --}}
            {{--            @enderror --}}
            <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
            </div>
        </form>
    </div>
@endsection
