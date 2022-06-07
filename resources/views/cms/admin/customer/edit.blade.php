@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.customer.index', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>

    <form method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf
        <div class="card mb-4">
            <div class="card-header">
                @if ($method === 'create')
                    新增
                @else
                    編輯
                @endif 帳號
            </div>
            <div class="card-body">
                <x-b-form-group name="acount_status" title="開通狀態">
                    <div class="px-1">
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                關閉
                                <input class="form-check-input @error('acount_status') is-invalid @enderror" value="{{\App\Enums\Customer\AccountStatus::close()->value}}"
                                       name="acount_status" type="radio" @if ($method === 'create' || ($method === 'edit' && !$data->acount_status)) checked @endif>
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                開放
                                <input class="form-check-input @error('acount_status') is-invalid @enderror" value="{{\App\Enums\Customer\AccountStatus::open()->value}}"
                                       name="acount_status" type="radio" @if ($method === 'edit' && $data->acount_status) checked @endif>
                            </label>
                        </div>
                    </div>
                </x-b-form-group>
                <x-b-form-group name="name" title="姓名" required="true">
                    <input class="form-control @error('name') is-invalid @enderror" name="name"
                           value="{{ old('name', $data->name ?? '') }}" />
                </x-b-form-group>
                @if ($method !== 'create')
                    <x-b-form-group name="loginMethods" title="帳號類型">
                        <div class="px-1">
                            <div class="col-form-label @error('loginMethods') is-invalid @enderror">
                                @foreach($loginMethods as $key => $loginMethod)
                                    @if($key > 0)
                                        {{ ',' }}
                                    @endif
                                    {{ old('loginMethod', App\Enums\Customer\Login::getDescription($loginMethod->login_method) ?? '') }}
                                @endforeach
                            </div>
                        </div>
                    </x-b-form-group>
                @endif
                <x-b-form-group name="sex" title="性別">
                    <div class="px-1">
                        @foreach (\App\Enums\Customer\Sex::asSelectArray() as $key => $value)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    {{$value}}
                                    <input class="form-check-input @error('sex') is-invalid @enderror" value="{{ $key }}"
                                           name="sex" type="radio" @if ($method !== 'create' && $key === $data->sex) checked @endif>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </x-b-form-group>
                <x-b-form-group name="email" title="帳號" required="true">
                    @if ($method === 'create')
                        <input class="form-control @error('email') is-invalid @enderror" name="email"
                               value="{{ old('email', $data->email ?? '') }}" />
                    @else
                        <div class="col-form-label">{{ $data->email ?? '' }}</div>
                    @endif
                </x-b-form-group>
                <x-b-form-group name="password" title="密碼" required="{{ $method === 'create' ? 'true' : 'false' }}">
                    <input class="form-control @error('password') is-invalid @enderror" type="password" name="password"
                           value="" />
                </x-b-form-group>
                <x-b-form-group name="password_confirmation" title="密碼確認">
                    <input class="form-control @error('password_confirmation') is-invalid @enderror" type="password"
                           name="password_confirmation" value="" />
                </x-b-form-group>
                <x-b-form-group name="phone" title="手機">
                    <input class="form-control @error('phone') is-invalid @enderror" name="phone"
                           value="{{ old('phone', $data->phone ?? '') }}" />
                </x-b-form-group>

                <x-b-form-group name="birthday" title="生日">
                    <input class="form-control @error('birthday') is-invalid @enderror" type="date" name="birthday"
                           @if($method !== 'create')
                               value="{{ old('birthday', explode(' ', $data->birthday)[0] ?? '') }}"
                           @endif
                    />
                </x-b-form-group>

                <x-b-form-group name="newsletter" title="訂閱電子報">
                    <div class="px-1">
                        @foreach (\App\Enums\Customer\Newsletter::asSelectArray() as $key => $value)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    {{$value}}
                                    <input class="form-check-input @error('newsletter') is-invalid @enderror" value="{{ $key }}"
                                           name="newsletter" type="radio" @if ($method !== 'create' && $key === $data->newsletter) checked @endif>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </x-b-form-group>

                @if ($method === 'edit')
                    <input type='hidden' name='id' value="{{ old('id', $id) }}" />
                @endif
                @error('id')
                <div class="alert alert-danger mt-3">{{ $message }}</div>
                @enderror
            </div>
        </div>
        @if($method !== 'create')
        <div class="card mb-4">
            <div class="card-body">
                <h6>消費記錄</h6>
                <dl class="row">
{{--                    <div class="col">--}}
{{--                        <dt>剩餘紅利點數</dt>--}}
{{--                        <dd>{{ number_format($data->bonus ?? '') }}</dd>--}}
{{--                    </div>--}}
                    <div class="col">
                        <dt>下單次數</dt>
                        <dd>{{ number_format($data->order_counts ?? '') }}</dd>
                    </div>
                    <div class="col-sm-5">
                        <dt>消費總額</dt>
                        <dd>{{ number_format($data->total_spending ?? '') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
        @endif
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
        <script>
            let cityElem = $('#city_id');
            let regionElem = $('#region_id')
            let addrInputElem = $('input[name=addr]');

            cityElem.on('change', function(e) {
                getRegionsAction($(this).val());
            });

            function getRegionsAction(city_id, region_id) {
                Addr.getRegions(city_id)
                    .then(re => {
                        Elem.renderSelect(regionElem, re.datas, {
                            default: region_id,
                            key: 'region_id',
                            value: 'region_title'
                        });
                    });
            }

            $('#format_btn').on('click', function(e) {
                let addr = addrInputElem.val();

                if (addr) {
                    Addr.addrFormating(addr).then(re => {
                        addrInputElem.val(re.data.addr);
                        if (re.data.city_id) {
                            cityElem.val(re.data.city_id);
                            getRegionsAction(re.data.city_id, re.data.region_id);

                        }
                    });
                }
            });
        </script>
    @endpush
@endonce
