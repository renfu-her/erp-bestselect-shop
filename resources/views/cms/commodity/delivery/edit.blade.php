@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">#{{ $sn }} 出貨審核</h2>
    <form action="">
        <div class="card shadow p-4 mb-4">
            <h6>商品列表</h6>
            <div class="table-responsive tableOverBox">
                <table class="table table-striped tableList">
                    <thead>
                        <tr>
                            <th style="width:3rem;">#</th>
                            <th>商品名稱</th>
                            <th>SKU</th>
                            <th>商品類型</th>
                            <th>訂購數量</th>
                            <th class="text-center" style="width: 15%">出貨數量</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ord_items_arr as $key => $ord)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>{{ $ord->product_title }}</td>
                            <td>{{ $ord->sku }}</td>
                            <td>(待處理)</td>
                            <td>{{ number_format($ord->qty) }}</td>
                            <td>
                                <input type="number" value="1" class="form-control form-control-sm text-center">
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td colspan="5" class="pt-0 ps-0">
                                <table class="table mb-0 table-sm table-hover border-start border-end">
                                    <thead>
                                        <tr class="border-top-0" style="border-bottom-color:var(--bs-secondary);">
                                            <td>入庫單</td>
                                            <td>倉庫</td>
                                            <td class="text-center">庫存</td>
                                            <td class="text-center" style="width: 15%">數量</td>
                                            <td>效期</td>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @foreach ($ord->receive_depot as $rec)
                                        <tr>
                                            <td>(待處理)</td>
                                            <td>{{ $rec->depot_name }}</td>
                                            <td class="text-center">(待處理)</td>
                                            <td class="text-center">
                                                <input type="number" value="{{ $rec->qty }}" class="form-control form-control-sm text-center">
                                            </td>
                                            <td>{{ date('Y/m/d', strtotime($rec->expiry_date)) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="border-top-0">
                                        <tr>
                                            <td colspan="5">
                                                <button type="button" class="btn btn-outline-primary btn-sm border-dashed w-100" style="font-weight: 500;"><i class="bi bi-plus-circle"></i> 新增</button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <div id="submitDiv">
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4">送出審核</button>
                <a href="{{ Route('cms.order.detail', ['id' => $order_id]) }}" class="btn btn-outline-primary px-4" role="button">前往訂單明細</a>
            </div>
        </div>
    </form>
@endsection