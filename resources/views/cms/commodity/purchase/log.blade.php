@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">#{{ $sn }} {{$title}}</h2>
    @if ($event === \App\Enums\Delivery\Event::purchase()->value)
        <x-b-pch-navi :id="$id"></x-b-pch-navi>
    @endif
    @if ($event === \App\Enums\Delivery\Event::consignment()->value)
        <x-b-consign-navi :id="$id"></x-b-consign-navi>
    @endif
    @if ($event === \App\Enums\Delivery\Event::csn_order()->value)
        <x-b-csnorder-navi :id="$id"></x-b-csnorder-navi>
    @endif

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
{{--                             @if($data->event == App\Enums\Delivery\Event::purchase()->value)--}}
                                 {{$data->title}}
                                 @if(isset($data->qty))
                                     @if($data->feature == App\Enums\Purchase\LogEventFeature::style_change_price()->value) 價錢:{{$data->qty}}
                                     @elseif($data->feature == App\Enums\Purchase\LogEventFeature::style_change_qty()->value) 數量:{{$data->qty}}
                                     @elseif($data->feature == App\Enums\Purchase\LogEventFeature::style_add()->value)
                                     @elseif($data->feature == App\Enums\Purchase\LogEventFeature::style_del()->value)
                                     @elseif($data->feature == App\Enums\Purchase\LogEventFeature::inbound_add()->value)@if(isset($data->qty)) 數量:{{$data->qty}} @endif
                                     @elseif($data->feature == App\Enums\Purchase\LogEventFeature::inbound_del()->value)@if(isset($data->qty)) 數量:{{$data->qty}} @endif
                                     @elseif($data->feature == App\Enums\Purchase\LogEventFeature::delivery()->value)@if(isset($data->qty)) 數量:{{$data->qty}} @endif
                                     @elseif($data->feature == App\Enums\Purchase\LogEventFeature::combo()->value)@if(isset($data->qty)) 數量:{{$data->qty}} @endif
                                     @elseif($data->feature == App\Enums\Purchase\LogEventFeature::send_back()->value)@if(isset($data->qty)) 數量:{{$data->qty}} @endif
                                     @elseif($data->feature == App\Enums\Purchase\LogEventFeature::consume_delivery()->value)@if(isset($data->qty)) 數量:{{$data->qty}} @endif
                                     @elseif($data->feature == App\Enums\Purchase\LogEventFeature::consume_send_back()->value)@if(isset($data->qty)) 數量:{{$data->qty}} @endif
                                     @else    @endif
                                 @endif
{{--                             @endif--}}
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
