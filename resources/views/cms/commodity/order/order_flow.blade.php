@extends('layouts.main')
@section('sub-content')

    <h2 class="mb-4">#{{ $order->sn }} 訂單紀錄</h2>

    <hr class="narbarBottomLine mb-3">

    <div class="card shadow p-4 mb-4">
        <div class="row justify-content-end mb-4">
            <div class="col-auto">
                顯示
                <select class="form-select d-inline-block w-auto" id="dataPerPageElem" aria-label="表格顯示筆數">
                    @foreach (config('global.dataPerPage') as $value)
                        <option value="{{ $value }}" @if ($data_per_page == $value) selected @endif>{{ $value }}</option>
                    @endforeach
                </select>
                筆
            </div>
        </div>
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList mb-1 table-sm small">
                <thead class="align-middle">
                    <tr>
                        <th scope="col" style="width:40px">#</th>
                        <th scope="col">更新日期</th>
                        <th scope="col">事件</th>
                        <th scope="col">操作者</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($dataList as $key =>$data)
                    <tr>
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ date('Y-m-d H:i:s', strtotime($data->updated_at)) }}</td>
                        <td>{{$data->status}}</td>
                        <td>{{$data->create_user_name}}</td>
                    </tr>
                 @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row flex-column-reverse flex-sm-row">
        <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
             @if($dataList)
            <div class="mx-3">共 {{ $dataList->lastPage() }} 頁(共找到 {{ $dataList->total() }} 筆資料)</div>
            {{-- 頁碼 --}}
            <div class="d-flex justify-content-center">{{ $dataList->links() }}</div>
             @endif
        </div>
    </div>

@endsection
@once
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endonce
