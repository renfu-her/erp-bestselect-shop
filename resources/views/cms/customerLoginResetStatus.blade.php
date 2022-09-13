
@extends('layouts.layout')
@section('content')

<div class="container mt-5">
    <div>
        <div class="alert alert-success m-2" role="alert">
            {{$status}}
        </div>

        <div class="text-center">
            <a href="{{ $formAction }}" target="_blank">返回喜鴻購物官網重新登入</a>
        </div>
    </div>
</div>

@endsection