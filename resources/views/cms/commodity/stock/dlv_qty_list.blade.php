@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">待出貨列表</h2>
    <h5 class="mb-3">{{ $title ?? '' }}</h5>

    <form id="search" method="GET" style="display:none">
        @csrf
        <div class="card shadow p-4 mb-4">
            <div class="col">
                <input type="hidden" name="data_per_page" value="{{ $searchParam['data_per_page'] }}" />
            </div>
        </div>
    </form>

    <div class="card shadow p-4 mb-4">
        <div class="row justify-content-end mb-4">
            <div class="col-auto">
                顯示
                <select class="form-select d-inline-block w-auto" id="dataPerPageElem" aria-label="表格顯示筆數">
                    @foreach (config('global.dataPerPage') as $value)
                        <option value="{{ $value }}" @if ($searchParam['data_per_page'] == $value) selected @endif>
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
                        <th scope="col" class="text-center">日期</th>
                        <th scope="col" class="text">事件</th>
                        <th scope="col" class="text-center">訂單號</th>
                        @if(false == isset($title))
                            <th scope="col" class="text">SKU</th>
                            <th scope="col" class="text">名稱</th>
                        @endif
                        <th scope="col" class="text-center">數量</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td class="text-center">{{ $data->created_at }}</td>
                            <td class="text">{{ \App\Enums\Delivery\Event::getDescription($data->event) }}</td>
                            <td class="text-center">
                                <a href="{{ Route('cms.stock.dlv_detail', ['delivery_id' => $data->delivery_id], true) }}"
                                   class="-text">{{ $data->event_sn?? '' }}</a>
                            </td>
                            @if(false == isset($title))
                                <td class="text">{{ $data->sku }}</td>
                                <td class="text">{{ $data->product_title }}</td>
                            @endif
                            <td class="text-center">{{ $data->stock_qty }}</td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>
    <div class="row flex-column-reverse flex-sm-row">
        <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
            @if ($dataList)
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
