@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">電子票券訂票錯誤紀錄</h2>

    <form id="search" action="{{ route('cms.tik_auto_order_error_log.index') }}" method="GET">
        @csrf
        <div class="card shadow p-4 mb-4">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="orderSn" class="form-label">訂單編號:</label>
                    <input type="text" class="form-control" id="orderSn" name="orderSn" value="{{ $searchParam['orderSn'] ?? '' }}"
                        placeholder="請輸入訂單編號" />
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">篩選</button>
                </div>
            </div>
            <div class="col">
                <input type="hidden" name="data_per_page" value="{{ $searchParam['data_per_page'] ?? 10 }}" />
            </div>
        </div>
    </form>

    <div class="card shadow p-4 mb-4">
        <div class="row justify-content-end mb-4">
            <div class="col-auto">
                顯示
                <select class="form-select d-inline-block w-auto" id="dataPerPageElem" aria-label="表格顯示筆數">
                    @foreach (config('global.dataPerPage') as $value)
                        <option value="{{ $value }}" @if (isset($searchParam['data_per_page']) && $searchParam['data_per_page'] == $value) selected @endif>
                            {{ $value }}</option>
                    @endforeach
                </select>
                筆
            </div>
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead class="small align-middle">
                    <tr>
                        <th scope="col" style="width:40px">#</th>
                        <th scope="col" class="text">時間</th>
                        <th scope="col" class="text-center">訂單編號</th>
                        <th scope="col" class="text">錯誤訊息</th>
                        <th scope="col" class="text">備註</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $key => $log)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td class="text">{{ $log->created_at }}</td>
                            <td class="text-center">{{ $log->sn }}</td>
                            <td class="text">{{ $log->error_message }}</td>
                            <td class="text">{{ $log->note }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="row flex-column-reverse flex-sm-row">
        <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
            @if (isset($logs) && method_exists($logs, 'links'))
                <div class="mx-3">共 {{ $logs->lastPage() }} 頁(共找到 {{ $logs->total() }} 筆資料)</div>
                {{-- 頁碼 --}}
                <div class="d-flex justify-content-center">{{ $logs->links() }}</div>
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

            // function submitAction(route, method) {
            //     console.log(route, method);
            //     document.getElementById("search").action = route;
            //     document.getElementById("search").setAttribute("method", method);
            //     document.getElementById("search").submit();
            // }
        </script>
    @endpush
@endOnce
