@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">編輯收款單</h2>

    <form method="POST" action="{{ $form_action }}">
        @csrf
        <div class="card shadow p-4 mb-4">
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">客戶 <span class="text-danger">*</span></label>
                    <select class="form-select -select2 -single" name="client_key" aria-label="客戶" data-placeholder="請選擇客戶" required>
                        <option value="" selected disabled>請選擇</option>
                        @foreach ($client as $value)
                            @php
                                $drawee_name = explode(' - ', $received_order->drawee_name);
                            @endphp
                            <option value="{{ $value['id'] . '|' . $value['name'] }}" {{ $value['id'] . '|' . $value['name'] == old('client_key', $received_order->drawee_id . '|' . $drawee_name[0]) ? 'selected' : '' }}>{{ $value['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">確認</button>
            <a href="{{ url()->previous() }}" class="btn btn-outline-primary px-4" role="button">取消</a>
        </div>
    </form>
@endsection

@once
    @push('sub-styles')

    @endpush
    @push('sub-scripts')
        <script>

        </script>
    @endpush
@endonce