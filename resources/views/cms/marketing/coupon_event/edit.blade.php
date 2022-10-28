@extends('layouts.main')
@section('sub-content')
    @if ($method === 'edit')
        <h2 class="mb-4">編輯 通關優惠券</h2>
    @else
        <h2 class="mb-4">新增 通關優惠券</h2>
    @endif

    <form id="form1" method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf
        <div class="card shadow p-4 mb-4">
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">活動名稱 <span class="text-danger">*</span></label>
                    <input class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title', $data->title ?? '') }}" name="title" type="text" placeholder="請輸入活動名稱"
                        required aria-label="活動名稱">
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">通關密語 <span class="text-danger">*</span></label>
                    <input class="form-control @error('sn') is-invalid @enderror" value="{{ old('sn', $data->sn ?? '') }}"
                        name="sn" type="text" placeholder="請輸入活動名稱" required aria-label="通關密語">
                    @error('sn')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">單次領取張數 <span class="text-danger">*</span></label>
                    <div class="input-group flex-nowrap">
                        <input type="number" class="form-control @error('qty_per_once') is-invalid @enderror"
                            name="qty_per_once" min="1" value="{{ old('qty_per_once', $data->qty_per_once ?? 1) }}"
                            placeholder="單次領取張數" required>
                        @error('qty_per_once')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">總張數<span class="text-danger">*</span><span class="small text-secondary">（0則表示無限制）</span></label>
                    <div class="input-group flex-nowrap">
                        <input type="number" class="form-control @error('qty_limit') is-invalid @enderror" name="qty_limit"
                            min="0" value="{{ old('qty_limit', $data->qty_limit ?? 0) }}" placeholder="總張數"
                            required>
                        @error('qty_limit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                @php
                    $start_date = isset($data) ? date('Y-m-d\Th:i', strtotime($data->start_date)) : null;
                    $end_date = isset($data) ? date('Y-m-d\Th:i', strtotime($data->end_date)) : null;
                @endphp
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">活動開始時間<span class="small text-secondary">（未填則表示現在）</span></label>
                    <div class="input-group has-validation">

                        <input type="datetime-local" name="start_date" value="{{ old('start_date', $start_date ?? '') }}"
                            editable class="form-control @error('start_date') is-invalid @enderror" aria-label="活動開始時間" />
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
                        <input type="datetime-local" name="end_date" value="{{ old('end_date', $end_date ?? '') }}"
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
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">指定贈送優惠券 <span class="text-danger">*</span></label>
                    <select name="discount_id"
                        class="form-select -select2 -single @error('discount_id') is-invalid @enderror"
                        aria-label="指定贈送優惠券">
                        <option value="" selected disabled>請選擇</option>
                        @foreach ($coupons as $key => $value)
                            <option value="{{ $value->id }}" @if (old('discount_id', $data->discount_id ?? '') == $value->id) selected @endif>
                                {{ $value->title }}</option>
                        @endforeach
                    </select>
                    @error('discount_value')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">是否重複領取</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" name="reuse">
                    </div>

                </div>

            </div>
        </div>

        <div id="submitDiv">
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
                <a href="{{ Route('cms.coupon-event.index') }}" class="btn btn-outline-primary px-4" role="button">返回列表</a>
            </div>
        </div>
    </form>
@endsection
@once
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endonce
