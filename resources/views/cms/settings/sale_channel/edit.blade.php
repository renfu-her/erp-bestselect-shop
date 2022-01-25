@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.sale_channel.index', [], true) }}" class="btn btn-primary" role="button">
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
            <x-b-form-group name="title" title="廠商名稱" required="true">
                <input class="form-control @error('title') is-invalid @enderror" name="title"
                    value="{{ old('title', $data->title ?? '') }}" />
            </x-b-form-group>
            <x-b-form-group name="contact_person" title="聯絡人" required="true">
                <input class="form-control @error('contact_person') is-invalid @enderror" name="contact_person"
                    value="{{ old('contact_person', $data->contact_person ?? '') }}" />
            </x-b-form-group>
            <x-b-form-group name="contact_tel" title="聯絡電話" required="true">
                <input class="form-control @error('contact_tel') is-invalid @enderror" name="contact_tel"
                    value="{{ old('contact_tel', $data->contact_tel ?? '') }}" />
            </x-b-form-group>
            <x-b-form-group name="chargeman" title="負責人" required="true">
                <input class="form-control @error('chargeman') is-invalid @enderror" name="chargeman"
                    value="{{ old('chargeman', $data->chargeman ?? '') }}" />
            </x-b-form-group>
            <x-b-form-group name="sales_type" title="銷售類型" required="true">
                <select class="form-select @error('sales_type') is-invalid @enderror" name="sales_type" id="sales_type">
                    @foreach (App\Enums\SaleChannel\SalesType::asArray() as $key => $val)
                        <option value="{{ $val }}" @if ($val == old('sales_type', $data->sales_type ?? '')) selected @endif>
                            {{ App\Enums\SaleChannel\SalesType::getDescription($val) }}</option>
                    @endforeach
                </select>
            </x-b-form-group>
            <x-b-form-group name="use_coupon" title="喜鴻紅利點數" required="true">
                <select class="form-select @error('use_coupon') is-invalid @enderror" name="use_coupon" id="use_coupon">
                    @foreach (App\Enums\SaleChannel\UseCoupon::asArray() as $key => $val)
                        <option value="{{ $val }}" @if ($val == old('use_coupon', $data->use_coupon ?? '')) selected @endif>
                            {{ App\Enums\SaleChannel\UseCoupon::getDescription($val) }}</option>
                    @endforeach
                </select>
            </x-b-form-group>
            <x-b-form-group name="is_realtime" title="類型" required="true">
                <div class="px-1">
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input class="form-check-input" type="radio" name="is_realtime" value="1"
                                @if (old('is_realtime', $data->is_realtime ?? '1') == 1) checked @endif>
                            即時
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input class="form-check-input" type="radio" name="is_realtime" value="0"
                                @if (old('is_realtime', $data->is_realtime ?? '') == 0) checked @endif>
                            非即時
                        </label>
                    </div>
                </div>
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
@once
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endonce
