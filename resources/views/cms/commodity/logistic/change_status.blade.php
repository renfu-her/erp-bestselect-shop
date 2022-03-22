@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">配送狀態</h2>

    <div class="pt-2 mb-3">
        <a href="{{ $lastPageAction }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>
    @error('error_msg')
    <div class="alert alert-danger" role="alert">
        {{ $message }}
    </div>
    @enderror
    <div>
        <div class="card shadow p-4 mb-4">
            <h6>狀態項目</h6>
            <div class="table-responsive tableOverBox">
                <table class="table tableList table-hover table-striped mb-1">
                    <thead>
                    <tr>
                        <th scope="col">狀態</th>
                        <th scope="col"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($logisticStatus as $key => $data)
                        <tr>
                            <td>{{$data}}</td>
                            <td>
                                <a href="{{ Route('cms.logistic.updateLogisticStatus',
                                    ['event' =>$event, 'eventId' => $eventId, 'deliveryId' => $delivery_id, 'statusCode' => $key], true) }}" class="btn btn-outline-primary">
                                    <i class="bi bi-plus-circle"></i> 加入
                                </a>
                            </td>

                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow p-4 mb-4">
        <h6>配送歷程</h6>
        <div class="table-responsive tableOverBox">
            <table class="table tableList table-hover table-striped mb-1">
                <thead>
                <tr>
                    <th scope="col" style="width:10%">#</th>
                    <th scope="col">狀態</th>
                    <th scope="col">人員</th>
                    <th scope="col">時間</th>
                    <th class="text-center">刪除</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($flowList as $key => $data)
                    <tr>
                        <td>{{$key + 1}}</td>
                        <td>{{$data->status}}</td>
                        <td>{{$data->user_name ?? ''}}</td>
                        <td>{{date('Y-m-d H:i:s', strtotime($data->created_at))}}</td>
                        <td class="text-center">
                            <a href="{{ Route('cms.logistic.deleteLogisticStatus', [
                                'event' => $event,
                                'eventId' => $eventId,
                                'deliveryId' => $delivery_id,
                                'flowId' => $data->id], true) }}"
                               class="icon icon-btn fs-5 text-danger rounded-circle border-0">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
@once
    @push('sub-scripts')
    @endpush
@endonce
