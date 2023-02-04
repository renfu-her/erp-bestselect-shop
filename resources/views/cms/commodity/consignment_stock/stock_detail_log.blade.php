@extends('layouts.main')
@section('sub-content')
    <span class="badge bg-primary mb-2"><h4 class="mb-0">{{$depot->name ?? ''}}</h4></span>
    <h2 class="mb-4">{{$title}} ({{ $productStyle->sku }})</h2>

    <form id="search" action="" method="GET">
        <input type="hidden" name="data_per_page" value="{{ $data_per_page }}" />
    </form>
    <div class="card shadow p-4 mb-4">
        <div class="row justify-content-end">
            <div class="col-auto">
                顯示
                <select class="form-select d-inline-block w-auto" id="dataPerPageElem" aria-label="表格顯示筆數">
                    @foreach (config('global.dataPerPage_big') as $value)
                        <option value="{{ $value }}" @if ($data_per_page == $value) selected @endif>{{ $value }}</option>
                    @endforeach
                </select>
                筆
            </div>
        </div>
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList mb-1 small">
                <thead>
                    <tr>
                        <th scope="col">時間</th>
                        <td scope="col" class="wrap">
                            <div class="fw-bold">採購單號</div>
                            <div>入庫單</div>
                        </td>
                        <th scope="col">倉庫</th>
                        <th scope="col">商品名稱</th>
                        <th scope="col">事件</th>
                        <th scope="col">動作</th>
                        <th scope="col">數量</th>
                        <th scope="col">操作者</th>
                        <th scope="col">備註</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sum = 0;
                    @endphp
                 @foreach ($purchaseLog as $key =>$data)
                    <tr>
                        <td class="wrap">
                            <div>{{date('Y/m/d', strtotime($data->created_at))}}</div>
                            <div>{{date('H:i:s', strtotime($data->created_at))}}</div>
                        </td>
                        <td class="wrap">
                            <div class="fw-bold">{{ $data->event_sn }}</div>
                            <div>{{ $data->inbound_sn ?? '-' }}</div>
                        </td>
                        <td>{{$data->depot_name}}</td>
                        <td class="wrap">{{$data->title}}</td>
                        <td>{{$data->event}}</td>
                        <td class="wrap">{{$data->feature}}</td>
                        <td class="text-end">{{number_format($data->qty)}}</td>
                        <td>{{$data->user_name}}</td>
                        <td>{{$data->note}}</td>
                    </tr>
                    @php
                        $sum += $data->qty;
                    @endphp
                 @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <th>總數</th>
                        <td class="text-end">{{ $sum }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="row flex-column-reverse flex-sm-row">
        <div class="col-auto">
            <a href="{{ $returnAction }}" class="btn btn-outline-primary px-4"
                role="button">返回列表</a>
        </div>
        <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
             @if($purchaseLog)
            <div class="mx-3">共 {{ $purchaseLog->lastPage() }} 頁(共找到 {{ $purchaseLog->total() }} 筆資料)</div>
            {{-- 頁碼 --}}
            <div class="d-flex justify-content-center">{{ $purchaseLog->links() }}</div>
             @endif
        </div>
    </div>

@endsection
@once
    @push('sub-scripts')
        <script>
            // 顯示筆數
            $('#dataPerPageElem').on('change', function(e) {
                $('input[name=data_per_page]').val($(this).val());
                $('#search').submit();
            });
        </script>
    @endpush
@endonce
