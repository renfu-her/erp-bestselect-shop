@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ Route('supplier.index', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>
    <div class="card">
        <div class="card-header">
            @if ($method === 'create') 新增 @else 編輯 @endif 廠商
        </div>
        <form class="card-body" method="post" action="{{ $formAction }}">
            @method('POST')
            @csrf
            <x-b-form-group name="name" title="廠商名稱" required="true">
                <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $data->name ?? '') }}" />
            </x-b-form-group>
            <x-b-form-group name="nickname" title="廠商簡稱" required="false">
                <input class="form-control @error('nickname') is-invalid @enderror" name="nickname" value="{{ old('nickname', $data->nickname ?? '') }}" />
            </x-b-form-group>
            <x-b-form-group name="vat_no" title="統編" required="true">
                <input class="form-control @error('vat_no') is-invalid @enderror" name="vat_no" value="{{ old('vat_no', $data->vat_no ?? '') }}" />
            </x-b-form-group>
            <x-b-form-group name="chargeman" title="負責人" required="true">
                <input class="form-control @error('chargeman') is-invalid @enderror" name="chargeman" value="{{ old('chargeman', $data->chargeman ?? '') }}" />
            </x-b-form-group>
            <x-b-form-group name="bank_cname" title="匯款銀行" required="true">
                <input class="form-control @error('bank_cname') is-invalid @enderror" name="bank_cname" value="{{ old('bank_cname', $data->bank_cname ?? '') }}" />
            </x-b-form-group>
            <x-b-form-group name="bank_code" title="匯款銀行代碼" required="true">
                <input class="form-control @error('bank_code') is-invalid @enderror" name="bank_code" value="{{ old('bank_code', $data->bank_code ?? '') }}" />
            </x-b-form-group>
            <x-b-form-group name="bank_acount" title="匯款戶名" required="true">
                <input class="form-control @error('bank_acount') is-invalid @enderror" name="bank_acount" value="{{ old('bank_acount', $data->bank_acount ?? '') }}" />
            </x-b-form-group>
            <x-b-form-group name="bank_numer" title="匯款帳號" required="true">
                <input class="form-control @error('bank_numer') is-invalid @enderror" name="bank_numer" value="{{ old('bank_numer', $data->bank_numer ?? '') }}" />
            </x-b-form-group>
            <x-b-form-group name="contact_tel" title="聯絡電話" required="true">
                <input class="form-control @error('contact_tel') is-invalid @enderror" name="contact_tel" value="{{ old('contact_tel', $data->contact_tel ?? '') }}" />
            </x-b-form-group>
            <x-b-form-group name="contact_address" title="聯絡地址" required="true">
                <input class="form-control @error('contact_address') is-invalid @enderror" name="contact_address" value="{{ old('contact_address', $data->contact_address ?? '') }}" />
            </x-b-form-group>
            <x-b-form-group name="contact_person" title="聯絡人" required="true">
                <input class="form-control @error('contact_person') is-invalid @enderror" name="contact_person" value="{{ old('contact_person', $data->contact_person ?? '') }}" />
            </x-b-form-group>
            <x-b-form-group name="email" title="電子郵件" required="true">
                <input class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $data->email ?? '') }}" />
            </x-b-form-group>
            <x-b-form-group name="memo" title="備註" required="false">
                <input class="form-control @error('memo') is-invalid @enderror" name="memo" value="{{ old('memo', $data->memo ?? '') }}" />
            </x-b-form-group>

            @if ($method === 'edit')
                <input type='hidden' name='id' value="{{ old('id', $id) }}" />
            @endif
            @error('id')
            <div class="alert alert-danger mt-3">{{ $message }}</div>
            @enderror
            <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
            </div>
        </form>
    </div>

@endsection
@once*
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endonce
