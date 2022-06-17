@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.customer-profit.index', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>

    <form method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf
        <div class="card mb-4">
            <div class="card-header">
                編輯
            </div>
            <div class="card-body">
                <div>{{ $customer->name }}</div>
                <x-b-form-group name="status" title="狀態" required="true">
                    <select class="form-select">
                        @foreach ($status as $key => $value)
                            <option value="{{ $key }}" @if ($data->status == $key) selected @endif>
                                {{ $value }}</option>
                        @endforeach
                    </select>
                </x-b-form-group>
                <x-b-form-group name="identity_id" title="身分證" required="true">
                    <input class="form-control @error('identity_id') is-invalid @enderror" name="identity_id"
                        value="{{ old('identity_id', $data->identity_id ?? '') }}" />
                </x-b-form-group>
                <x-b-form-group name="parent_profit_rate" title="上一代分潤(%)" required="true">
                    <input class="form-control @error('parent_profit_rate') is-invalid @enderror" name="parent_profit_rate"
                        value="{{ old('parent_profit_rate', $data->parent_profit_rate ?? '') }}" />
                </x-b-form-group>
                <x-b-form-group name="profit_rate" title="分潤(%)" required="true">
                    <input class="form-control @error('profit_rate') is-invalid @enderror" name="profit_rate"
                        value="{{ old('profit_rate', $data->profit_rate ?? '') }}" />
                </x-b-form-group>
                <x-b-form-group name="profit_type" title="分潤回饋方式">
                    <div class="form-check">
                        @foreach ($profitType as $key => $pType)
                            <div class="form-check">
                                <input class="form-check-input" type="radio" value="{{ $key }}" name="profit_type"
                                    id="profit_type_{{ $key }}" @if ($key == old('profit_type', $data->profit_type ?? 'dividend')) checked @endif>
                                    <label class="form-check-label" for="profit_type_{{ $key }}">
                                    {{ $pType }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </x-b-form-group>
                <x-b-form-group name="has_child" title="是否有下一代">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" name="has_child" id="has_child">
                        <label class="form-check-label" for="has_child">
                            是
                        </label>
                    </div>
                </x-b-form-group>
                <x-b-form-group name="bank_id" title="銀行" required="true">
                    <select class="form-select">
                        @foreach ($banks as $key => $bank)
                            <option value="{{ $bank->id }}" @if ($data->bank_id == $bank->id) selected @endif>
                                {{ $bank->code }} {{ $bank->title }}</option>
                        @endforeach
                    </select>
                </x-b-form-group>
                <x-b-form-group name="bank_account" title="銀行帳號" required="true">
                    <input class="form-control @error('bank_account') is-invalid @enderror" name="bank_account"
                        value="{{ old('bank_account', $data->bank_account ?? '') }}" />
                </x-b-form-group>
                <x-b-form-group name="bank_account_name" title="銀行戶名" required="true">
                    <input class="form-control @error('bank_account_name') is-invalid @enderror" name="bank_account_name"
                        value="{{ old('bank_account_name', $data->bank_account_name ?? '') }}" />
                </x-b-form-group>
                @php
                    $imgTitle = ['身分證正面', '身分證反面', '存摺封面'];
                @endphp
                @for ($i = 1; $i < 4; $i++)
                    <h3>{{ $imgTitle[$i - 1] }}</h3>
                    @if ($data->{"img$i"})
                        <img src="{{ asset($data->{"img$i"}) }}" />
                    @endif
                @endfor
            </div>
        </div>

        <div class="d-flex justify-content-end">
            @if (isset($bind))
                <input type="hidden" name="bind" value="{{ $bind }}">
            @endif
            <button type="submit" class="btn btn-primary px-4">儲存</button>
        </div>
    </form>
@endsection
@once
    @push('sub-scripts')
    @endpush
@endonce
