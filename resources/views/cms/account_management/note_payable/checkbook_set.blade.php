@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">列印票據</h2>

    <form method="POST" action="{{ $form_action }}" target="_blank">
        @csrf
        <div class="card shadow p-4 mb-4">
            <h6>列印條件</h6>
            <div class="row">
                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">票據英文字軌 <span class="text-danger">*</span></label>
                    <input id="alph_check" class="form-control @error('alphabet_character') is-invalid @enderror" type="text" name="alphabet_character" value="{{ old('alphabet_character', 'AA') }}" placeholder="請輸入票據英文字軌" maxlength="2" style="text-transform: uppercase;" required>
                    <div class="invalid-feedback">
                        @error('alphabet_character')
                        {{ $message }}
                        @enderror
                    </div>
                </div>

                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">票據起始號碼 <span class="text-danger">*</span></label>
                    <input type="number" step="1" min="0" max="9999999" maxlength="7" class="form-control @error('min_number') is-invalid @enderror" name="min_number" value="{{ old('min_number', 0) }}" placeholder="請輸入票據起始號碼" required>
                    <div class="invalid-feedback">
                        @error('min_number')
                        {{ $message }}
                        @enderror
                    </div>
                </div>

                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">票據結束號碼 <span class="text-danger">*</span></label>
                    <input type="number" step="1" min="0" max="9999999" maxlength="7" class="form-control @error('max_number') is-invalid @enderror" name="max_number" value="{{ old('max_number', 0000000) }}" placeholder="請輸入票據結束號碼" required>
                    <div class="invalid-feedback">
                        @error('max_number')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <button type="submit" class="btn btn-primary px-4">確認送出</button>
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
            $('#alph_check').on('keyup paste blur', function (e) {
                $(this).val( $(this).val().toUpperCase() );

                if ((e.which >= 65 && e.which <= 90) || (event.charCode >= 97 && event.charCode <= 122) ){
                    return true;

                } else {
                    e.preventDefault();
                    return false;
                }
            });
        </script>
    @endpush
@endonce
