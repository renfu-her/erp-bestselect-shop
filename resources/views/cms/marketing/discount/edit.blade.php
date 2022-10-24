@extends('layouts.main')
@section('sub-content')
    @if ($method === 'edit')
        <h2 class="mb-4">活動名稱</h2>
    @else
        <h2 class="mb-4">新增 全館優惠</h2>
    @endif

    <form id="form1" method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf
        @php
            $editBlock = $method === 'edit' ? 'disabled' : '';
        @endphp
        @if ($method === 'edit')
            <input type="hidden" name="" value="">
        @endif

        <div class="card shadow p-4 mb-4">
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">活動名稱 <span class="text-danger">*</span></label>
                    <input class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title', $data->title ?? '') }}" name="title" type="text" placeholder="請輸入活動名稱"
                        required aria-label="活動名稱" {{ $editBlock }}>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">消費金額 <span class="text-danger">*</span></label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                        <input type="number" class="form-control @error('min_consume') is-invalid @enderror"
                            name="min_consume" min="0" value="{{ old('min_consume', $data->min_consume ?? '') }}"
                            placeholder="請輸入消費金額" required {{ $editBlock }}>
                        @error('min_consume')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">活動開始時間<span class="small text-secondary">（未填則表示現在）</span></label>
                    <div class="input-group has-validation">
                        <input type="datetime-local" name="start_date"
                          value="{{ old('start_date',date('Y-m-d\Th:i', strtotime($data->start_date)) ?? '') }}" editable
                            class="form-control @error('start_date') is-invalid @enderror" aria-label="活動開始時間" />
                        <button class="btn btn-outline-secondary icon" type="button" data-clear data-bs-toggle="tooltip"
                            title="清空時間"><i class="bi bi-calendar-x"></i>
                        </button>
                        <div class="invalid-feedback">
                            @error('start_date')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">活動結束時間<span class="small text-secondary">（未填則表示不會結束）</span></label>
                    <div class="input-group has-validation">
                        <input type="datetime-local" name="end_date" value="{{ old('end_date',date('Y-m-d\Th:i', strtotime($data->end_date)) ?? '') }}"
                            class="form-control @error('end_date') is-invalid @enderror" aria-label="活動結束時間" editable />
                        <button class="btn btn-outline-secondary icon" type="button" data-clear data-bs-toggle="tooltip"
                            title="清空時間"><i class="bi bi-calendar-x"></i>
                        </button>
                        <div class="invalid-feedback">
                            @error('end_date')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
              
                <fieldset class="col-12 mb-1">
                    <legend class="col-form-label p-0 mb-2">優惠方式 <span class="text-danger">*</span></legend>
                    <div class="px-1 pt-1">
                        @foreach ($dis_methods as $key => $value)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="method_code" type="radio" {{ $editBlock }}
                                        value="{{ $key }}" @if (old('method_code', $data->method_code ?? 'cash') == $key) checked @endif
                                        required>
                                    {{ $value }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>

                {{-- 優惠方式：金額 cash --}}
                <div class="row mb-3 border rounded mx-0 px-0 pt-2" data-method="cash" hidden>
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">折扣金額 <span class="text-danger">*</span></label>
                        <div class="input-group has-validation">
                            <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                            <input type="number" name="discount_value" placeholder="請輸入折扣金額" {{ $editBlock }}
                                class="form-control @error('discount_value') is-invalid @enderror" min="0"
                                value="{{ old('method_code', $data->method_code ?? '') === 'cash'? old('discount_value', $data->discount_value ?? ''): '' }}">
                            <div class="invalid-feedback">
                                @if (old('method_code', $data->method_code ?? '') === 'cash')
                                    @error('discount_value')
                                        {{ $message }}
                                    @enderror
                                @endif
                            </div>
                        </div>
                    </div>
                    <fieldset class="col-12 col-sm-6 mb-3">
                        <legend class="col-form-label p-0 mb-2">&nbsp;</legend>
                        <div class="px-1 pt-1">
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="is_grand_total" type="checkbox" value="1"
                                        {{ $editBlock }} @if (old('is_grand_total', $data->is_grand_total ?? '') == '1') checked @endif norequired>
                                    累計折扣
                                    <i class="bi bi-info-circle" data-bs-toggle="tooltip"
                                        title="累積消費金額將可累積折扣，例：消費金額 100 元折 10 元，消費 200 元折 20 元，以此類推" data-bs-placement="right"></i>
                                </label>
                            </div>
                        </div>
                    </fieldset>
                </div>

                {{-- 優惠方式：百分比 percent --}}
                <div class="row mb-3 border rounded mx-0 px-0 pt-2" data-method="percent" hidden>
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">折扣百分比 <span class="text-danger">*</span>
                            <i class="bi bi-info-circle" data-bs-toggle="tooltip"
                                title="例：100 元商品打 8 折為 80 元，請輸入數字 80，等同 80%" data-bs-placement="right"></i>
                        </label>
                        <div class="input-group has-validation">
                            <input type="number" name="discount_value" {{ $editBlock }}
                                class="form-control @error('discount_value') is-invalid @enderror" min="1" max="100"
                                value="{{ old('method_code', $data->method_code ?? '') === 'percent'? old('discount_value', $data->discount_value ?? ''): '' }}"
                                placeholder="請輸入百分比 1 ~ 100">
                            <span class="input-group-text"><i class="bi bi-percent"></i></span>
                            <div class="invalid-feedback">
                                @if (old('method_code', $data->method_code ?? '') === 'percent')
                                    @error('discount_value')
                                        {{ $message }}
                                    @enderror
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 優惠方式：優惠劵 coupon --}}
                <div class="row mb-3 border rounded mx-0 px-0 pt-2" data-method="coupon" hidden>
                    <div class="col-12">
                        <label class="form-label">指定贈送優惠券 <span class="text-danger">*</span></label>
                        <select name="discount_value" {{ $editBlock }}
                            class="form-select -select2 -single @error('discount_value') is-invalid @enderror"
                            aria-label="指定贈送優惠券">
                            <option value="" selected disabled>請選擇</option>
                            @foreach ($coupons as $key => $value)
                                <option value="{{ $value->id }}" @if (old('discount_value', $data->discount_value ?? '') == $value->id) selected @endif>
                                    {{ $value->title }}</option>
                            @endforeach
                        </select>
                        @error('discount_value')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 mb-3">
                        <p class="fw-light small mark mb-0">
                            <i class="bi bi-exclamation-diamond-fill mx-2 text-warning"></i>
                            此處優惠券為 <a href="{{ Route('cms.promo.create') }}">行銷設定>優惠券/序號</a>
                            設定之優惠券，若無請先前往新增（以上未儲存資料將不保留）。
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div id="submitDiv">
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
                <a href="{{ Route('cms.discount.index') }}" class="btn btn-outline-primary px-4" role="button">返回列表</a>
            </div>
        </div>
    </form>
@endsection
@once
    @push('sub-scripts')
        <script>
            const AbleControl = {
                required: true,
                disabled: false
            };
            const DisabledControl = {
                required: false,
                disabled: true
            };
            const editBlock = @json($editBlock);

            // init
            setMethod();
            // 優惠方式
            $('input[name="method_code"]').on('change', function() {
                setMethod();
            });

            // 設定優惠方式
            function setMethod() {
                const method = $('input[name="method_code"]:checked').val();

                // hidden
                $(`div[data-method]:not([data-method="${method}"])`).prop('hidden', true);
                $(`div[data-method]:not([data-method="${method}"])`).find('input, select').prop(DisabledControl);

                // shown
                $(`div[data-method="${method}"]`).prop('hidden', false);
                $(`div[data-method="${method}"]`).find('input, select').prop(AbleControl);
                $(`div[data-method="${method}"]`).find('[norequired]').prop('required', false);
                if (editBlock) {
                    $(`div[data-method="${method}"]`).find('input:not([editable]), select:not([editable])').prop(DisabledControl);
                }
            }
        </script>
    @endpush
@endonce
