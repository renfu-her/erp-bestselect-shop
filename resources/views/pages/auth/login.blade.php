@extends('layouts.layout')
@section('content')

    <body class="text-center">
        <main class="form-signin">
            <form method="POST" id="form" action="{{ $action }}">
                @method('POST')
                @csrf
                <img class="mb-4 w-100" src="{{ Asset('images/Best-logo.png') }}" alt="喜鴻國際 Logo">
                <h1 class="h3 fw-normal">喜鴻國際 購物系統</h1>
                <h2 class="h5 fw-light mb-3">
                    <span @class([
                        'rounded-pill px-3 py-1',
                        'bg-User text-white' => !$title,
                        'bg-Admin' => $title == '管理員',
                        'bg-Deliveryman' => $title == '物流人員'
                    ])>
                        {{ $title ? $title : '託運商' }}
                    </span>
                </h2>
                <div class="form-floating">
                    <input type="text" class="form-control @error('account') is-invalid @enderror" id="account" name="account">
                    <label for="account">帳號</label>
                    @error('account')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password">
                    <label for="password">密碼</label>
                </div>
                @error('login-error')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
                <div class="checkbox mb-3">
                    <label>
                        <input type="checkbox" value="remember-me" name="remember_me"> 記住我
                    </label>
                </div>
                <button class="w-100 btn btn-lg btn-primary" type="submit">登入</button>
                <div class="pt-3">
                    @foreach ($otherLogins as $key => $value)
                        <a class="px-3" href="{{ $value['url'] }}">{{ $value['title'] }}</a>
                    @endforeach
                </div>
                <p class="mt-5 mb-3 text-muted">&copy; 2017–2021</p>
            </form>

        </main>
    </body>

@endsection


@once
    @push('styles')
        <link href="{{ Asset('css/signin.css') }}" rel="stylesheet">
        <style>
            .bd-placeholder-img {
                font-size: 1.125rem;
                text-anchor: middle;
                -webkit-user-select: none;
                -moz-user-select: none;
                user-select: none;
            }

            @media (min-width: 768px) {
                .bd-placeholder-img-lg {
                    font-size: 3.5rem;
                }
            }

        </style>
    @endpush
    @push('scripts')
        <script>


        </script>
    @endpush
@endonce
