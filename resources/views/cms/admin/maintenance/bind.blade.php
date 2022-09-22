@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.dashboard', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回首頁
        </a>
    </div>

    <form method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf

        <div class="card mb-4">
            <div class="card-header">
                @if(!is_null($customer))重新@endif綁定消費者
            </div>
            <div class="card-body">
                <x-b-form-group name="name" title="姓名">
                    <div class="form-control" readonly>{{ $data->name }}</div>
                </x-b-form-group>
                <br>
                @php
                    if (is_null($customer)) {
                        $title = '消費者Email';
                    } else {
                        $title = '已綁定消費者帳號：' . $customer->name . '(' . $customer->email  . '）';
                    }
                @endphp
                <x-b-form-group name="email" title="{{ $title }}">
                    <div class="input-group mb-3">
                        <input type="email"
                               class="form-control"
                               name="email"
                               placeholder="@if($customer)請輸入新的消費者Email帳號，進行重新綁定@else 請輸入Email @endif"
                        >
                        <input type="hidden" name="rebinding" value="{{ $customer ? '1' : '0' }}">
                        <button class="btn btn-outline-success" type="button" id="check">查驗</button>
                    </div>
                </x-b-form-group>
                @error('email')
                    {{ $message }}
                @enderror
                <div class="emailAlert d-none" role="alert"></div>
                <div class="alert d-none" role="alert"></div>
                <div class="d-flex justify-content-end mt-3">
                    <a href="{{ route('cms.customer.create', ['bind' => 1]) }}"
                        class="new_customer btn btn-primary px-4 -noData d-none">建立消費者並綁定</a>
                    <button type="submit" class="btn btn-primary px-4 -bind d-none">儲存</button>
                </div>
            </div>
        </div>
    </form>
@endsection
@once
    @push('sub-scripts')
        <script>
            let bindUrl = @json(route('api.cms.user.check-customer-bind'));

            let emailElem = $('input[name=email]');

            let mail_format =
                /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

            //點擊「建立消費者並綁定」，順便回傳Email參數給後端處理
            $('.new_customer').on('click', function() {
                $(this).attr('href', function() {
                    return this.href + '&email=' + emailElem.val();
                });
            })

            $('#check').on('click', function() {
                let email = emailElem.val();

                if (email.match(mail_format)) {
                    checkProcess(email)
                        .then(re => {
                            processCase(re);
                        })
                        .catch(err => {
                            console.log(err);
                        });
                } else {
                    alert('Email格式錯誤，請重新輸入');
                }
            })

            function checkProcess(email) {
                return axios.get(bindUrl + "/" + email).then((re) => {
                    if (re.status === 200) {
                        // console.log(re.data.datas);
                        return re.data;
                    } else {
                        return Promise.reject(re);
                    }
                });
            }

            function processCase(data) {
                console.log(data);
                $('div.alert').text(data.message)
                switch (data.status) {
                    case '0':
                        $('div.alert').addClass('alert-success').removeClass('alert-danger d-none');
                        $('a.-noData').addClass('d-none');
                        $('button.-bind').removeClass('d-none');
                        break;
                    //沒有此消費者Email，顯示「建立消費者並綁定」
                    case 'no_data':
                        $('div.alert').addClass('alert-danger').removeClass('alert-success d-none');
                        $('a.-noData').removeClass('d-none');
                        $('button.-bind').addClass('d-none');
                        break;

                    default:
                        $('div.alert').addClass('alert-danger').removeClass('alert-success d-none');
                        $('a.-noData, button.-bind').addClass('d-none');
                        break;
                }
            }
        </script>
    @endpush
@endonce
