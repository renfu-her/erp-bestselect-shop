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
                <x-b-form-group title="姓名">
                    <input class="form-control" type="text" disabled value="{{ $customer->name }}">
                </x-b-form-group>
                <x-b-form-group name="status" title="狀態" required="true">
                    <select class="form-select" name="status">
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
                <x-b-form-group title="推薦者">
                    <input class="form-control" type="text" disabled value="{{ $customer->recommend_name ?? '無' }}">
                </x-b-form-group>
                <x-b-form-group name="parent_profit_rate" title="上一代分潤(%)" required="{{ $customer->recommend_name ? 'true' : 'false' }}">
                    <input class="form-control @error('parent_profit_rate') is-invalid @enderror" name="parent_profit_rate"
                        @if (is_null($customer->recommend_name)) readonly @endif type="number" min="0" max="100"
                        value="{{ $customer->recommend_name ? old('parent_profit_rate', $data->parent_profit_rate ?? '20') : '0' }}" />
                </x-b-form-group>
                <x-b-form-group name="profit_rate" title="分潤(%)">
                    <input class="form-control" name="profit_rate" readonly
                        value="{{ old('parent_profit_rate', 100 - $data->parent_profit_rate) }}" type="number" />
                </x-b-form-group>
                <x-b-form-group name="profit_type" title="分潤回饋方式">
                    <div class="px-1 pt-1">
                        @foreach ($profitType as $key => $pType)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="profit_type" value="{{ $key }}"
                                        @if ($key === 'dividend') disabled @endif
                                        type="radio" @if ($key == old('profit_type', $data->profit_type ?? 'cash')) checked @endif>
                                    {{ $pType }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </x-b-form-group>
                <x-b-form-group name="has_child" title="是否有下一代">
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline form-switch form-switch-lg">
                            <input class="form-check-input" type="checkbox" name="has_child" value="1"
                                @if (old('has_child', $data->has_child ?? '') == '1') checked @endif>
                        </div>
                    </div>
                </x-b-form-group>
                <x-b-form-group name="bank_id" title="銀行" required="true">
                    <select class="form-select" name="bank_id">
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
                @foreach ($imgTitle as $i => $item)
                    <div class="form-group">
                        <label class="col-form-label">{{ $item }}
                            @if ($data->{"img$i"})
                                <a href="{{ asset($data->{"img$i"}) }}" data-bs-toggle="tooltip" title="下載" 
                                    class="icon icon-btn fs-5 text-primary rounded-circle border-0" download>
                                    <i class="bi bi-cloud-arrow-down"></i>
                                </a>
                            @endif
                        </label>
                        <div class="uploadPreview">
                            @if ($data->{"img$i"})
                                <img src="{{ asset($data->{"img$i"}) }}" />
                            @endif
                        </div>
                    </div>
                @endforeach
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
    @push('sub-styles')
        <style>
            .uploadPreview {
                border-radius: 5px;
                background-color: #cccccc;
                min-height: 200px;
                max-height: 300px;
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
            }

            .uploadPreview img {
                width: auto;
                max-width: 100%;
                height: auto;
                max-height: 300px;
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            $('input[name="parent_profit_rate"]').on('change', function () {
                const parent_rate = Number($(this).val());
                if (isFinite(parent_rate)) {
                    $('input[name="profit_rate"]').val(100 - parent_rate);
                }
            });
        </script>
    @endpush
@endonce
