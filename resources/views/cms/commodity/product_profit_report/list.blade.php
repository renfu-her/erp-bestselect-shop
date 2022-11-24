@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">售價利潤報表</h2>
    <div class="card shadow p-4 mb-4">
        <h6>搜尋條件</h6>
        <form>
            @csrf
            <div class="col-12 col-sm-6 mb-3">
                <fieldset class="col-12 mb-3">
                    <legend class="col-form-label p-0 mb-2">利潤排序</legend>
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="profit" type="radio" value="price_profit"
                                       checked>
                                售價利潤
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="profit" type="radio" value="dealer_price_profit">
                                經銷價利潤
                            </label>
                        </div>
                    </div>
                </fieldset>
                <fieldset class="col-12 mb-3">
                    <legend class="col-form-label p-0 mb-2">理貨倉庫存</legend>
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="stock_status" type="radio" value="in_stock"
                                       checked>
                                有
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="stock_status" type="radio" value="out_of_stock">
                                無
                            </label>
                        </div>
                    </div>
                </fieldset>
            </div>
            <div class="col">
                {{--            <input type="hidden" name="data_per_page" value="{{ $data_per_page }}" />--}}
                <button type="submit" class="btn btn-primary px-4">搜尋</button>
            </div>
        </form>
    </div>

    <div class="card shadow p-4 mb-4">
        @can('cms.product-profit.export_excel')
            <div class="col">
                <a href="{{ Route('cms.product-profit-report.export-excel', ['stock_status' => 'all']) }}" class="btn btn-outline-success export">
                    <i class="bi"></i> 匯出售價利潤報表
                </a>
            </div>
        @endcan
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead class="small align-middle">
                <tr>
                    <th scope="col" style="width:40px">#</th>
                    <th scope="col">商品名稱</th>
                    <th scope="col">款式</th>
                    <th scope="col">售價</th>
                    {{--                    <th scope="col">cost</th>--}}
                    <th scope="col">售價利潤</th>
                    <th scope="col">經銷價</th>
                    <th scope="col">經銷價利潤</th>
                    <th scope="col">理貨倉庫存</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($dataList as $key => $data)
                    <tr>
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>
                            <a href="{{ Route('cms.product.edit', ['id' => $data->product_id], true) }}">{{ $data->product_title }}</a>
                        </td>
                        <td>{{ $data->sku }}</td>
                        <td>{{ number_format($data->price) }}</td>
                        {{--                        <td>{{ $data->estimated_cost }}</td>--}}
                        <td>
                            {{ $data->price_profit }}%
                        </td>
                        <td>{{ number_format($data->dealer_price) }}</td>
                        <td>
                            {{ $data->dealer_price_profit }}%
                        </td>
                        <td>{{ $data->total_in_stock_num }}</td>
                    </tr>
                @endforeach

                </tbody>
                <tfoot>
                <tr>
                </tr>
                </tfoot>
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
    @push('sub-styles')
    @endpush
    @push('sub-scripts')
        <script></script>
    @endpush
@endOnce
