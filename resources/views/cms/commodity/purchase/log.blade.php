@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">採購單 {{ $purchaseData->purchase_sn }}</h2>
    <x-b-pch-navi :id="$id"></x-b-pch-navi>

    <div class="card shadow p-4 mb-4">
        <h6>最近變更紀錄</h6>
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList mb-1">
                <thead>
                <tr>
                    <th scope="col">時間</th>
                    <th scope="col">動作</th>
                    <th scope="col">操作者</th>
                </tr>
                </thead>
                <tbody>
                 @foreach ($purchaseLog as $key =>$data)
                     <tr>
                         <td>{{$data->created_at}}</td>
                         <td>{{App\Enums\Purchase\LogEventFeature::getDescription($data->feature)}}
                             @if($data->event == App\Enums\Purchase\LogEvent::style()->key)
                                 {{$data->title}} @if(isset($data->qty)) 改為:{{$data->qty}} @endif
                             @elseif($data->event == App\Enums\Purchase\LogEvent::inbound()->key)
                                 {{$data->title}} @if(isset($data->qty)) 改為:{{$data->qty}} @endif
                             @endif
                         </td>
                         <td>{{$data->user_name}}</td>
                     </tr>
                 @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row flex-column-reverse flex-sm-row">
        <div class="col-auto">
            <a href="{{ Route('cms.purchase.index', [], true) }}" class="btn btn-outline-primary px-4"
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
