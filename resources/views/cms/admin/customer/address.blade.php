@extends('layouts.main')
@section('sub-content')
    <div>
        <x-b-customer-navi :customer="$customer"></x-b-customer-navi>
    </div>
    <div class="card shadow p-4 mb-4">
        <div class="table-responsive tableOverBox">
            <table class="table tableList">
                <thead>
                <tr>
                    <th scope="col">預設地址</th>
                    <th scope="col">姓名</th>
                    <th scope="col">聯絡電話</th>
                    <th scope="col">地址</th>
                </tr>
                </thead>
                <tbody>
                @if(!is_null($defaultAddress))
                    <tr class="table-danger">
                        <td class="">✓</td>
                        <td>{{$defaultAddress->name ?? ''}}</td>
                        <td>{{$defaultAddress->phone ?? ''}}</td>
                        <td>{{$defaultAddress->address ?? ''}}</td>
                    </tr>
                @endif
                @foreach ($otherAddress as $data)
                    <tr>
                        <td></td>
                        <td>{{ $data->name ?? ''}}</td>
                        <td>{{ $data->phone ?? ''}}</td>
                        <td>{{ $data->address ?? ''}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
