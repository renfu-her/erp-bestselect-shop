@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">列印票據</h2>

    <form method="POST" action="{{ $form_action }}">
        @csrf
        <div class="card shadow p-4 mb-4">
            <h6></h6>
            <div class="row">
                {{--
                <div class="col-12 col-sm-12 mb-3">
                    <label class="form-label">票據號碼</label>
                    <div class="input-group has-validation">
                        <input type="number" step="1" min="0" class="form-control @error('payable_min_price') is-invalid @enderror" name="payable_min_price" value="{{ $cond['payable_min_price'] }}" aria-label="起始票據號碼">
                        <input type="number" step="1" min="0" class="form-control @error('payable_max_price') is-invalid @enderror" name="payable_max_price" value="{{ $cond['payable_max_price'] }}" aria-label="結束票據號碼">
                        <div class="invalid-feedback">
                            @error('payable_min_price')
                                {{ $message }}
                            @enderror
                            @error('payable_max_price')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
                --}}

                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">每頁筆數 <span class="text-danger">*</span></label>
                    <input class="form-control @error('item_per_page') is-invalid @enderror" type="number" step="1" min="1" name="item_per_page" value="{{ old('item_per_page', 8) }}" placeholder="請輸入每頁筆數">
                    <div class="invalid-feedback">
                        @error('item_per_page')
                        {{ $message }}
                        @enderror
                    </div>
                </div>

                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">每頁高度 <span class="text-danger">*</span></label>
                    <input class="form-control @error('page_height') is-invalid @enderror" type="number" step="0.1" min="0" name="page_height" value="{{ old('page_height', 27.7) }}" placeholder="請輸入每頁高度">
                    <div class="invalid-feedback">
                        @error('page_height')
                        {{ $message }}
                        @enderror
                    </div>
                </div>

                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">每格高度 <span class="text-danger">*</span></label>
                    <input class="form-control @error('row_height') is-invalid @enderror" type="number" step="1" min="0" name="row_height" value="{{ old('row_height', 120) }}" placeholder="請輸入每格高度">
                    <div class="invalid-feedback">
                        @error('row_height')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>

            <div class="col">
                <button type="submit" class="btn btn-primary px-4">確認送出</button>
            </div>
        </div>
    </form>
@endsection

@once
    @push('sub-styles')
        <style>

        </style>
    @endpush

    @push('sub-scripts')
        <script>

        </script>
    @endpush
@endonce
