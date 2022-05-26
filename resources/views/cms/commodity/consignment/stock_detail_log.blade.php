@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">{{$title}} {{ $productStyle->sku }}</h2>

    <div class="card shadow p-4 mb-4">
        <h6>明細</h6>
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList mb-1">
                <thead>
                <tr>
                    <th scope="col">時間</th>
                    <th scope="col">倉庫</th>
                    <th scope="col">商品名稱</th>
                    <th scope="col">事件</th>
                    <th scope="col">動作</th>
                    <th scope="col">數量</th>
                    <th scope="col">操作者</th>
                </tr>
                </thead>
                <tbody>
                 @foreach ($purchaseLog as $key =>$data)
                     <tr>
                         <td>{{$data->created_at}}</td>
                         <td>{{$data->depot_name}}</td>
                         <td>{{$data->title}}</td>
                         <td>{{$data->event}}</td>
                         <td>{{$data->feature}}</td>
                         <td>{{$data->qty}}</td>
                         <td>{{$data->user_name}}</td>
                     </tr>
                 @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row flex-column-reverse flex-sm-row">
        <div class="col-auto">
            <a href="{{ $returnAction }}" class="btn btn-outline-primary px-4"
                role="button">返回列表</a>
        </div>
        <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
            {{-- @if($dataList) --}}
            <div class="mx-3">共 1 頁(共找到 10 筆資料)</div>
            {{-- 頁碼 --}}
            <div class="d-flex justify-content-center"></div>
            {{-- @endif --}}
        </div>
    </div>

@endsection
@once
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endonce
