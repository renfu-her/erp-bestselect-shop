@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">{{ $pageTitle }}</h2>
    <div class="card shadow p-4 mb-4">
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead class="small align-middle">
                    <tr>
                        <th scope="col" style="width:40px">#</th>
                        <th scope="col">商品款式</th>
                        <th scope="col" class="text-center lh-1 table-success">線上<br class="d-block d-xl-none">營業額</th>
                        <th scope="col" class="text-center lh-1 table-success">線上<br class="d-block d-xl-none">毛利</th>
                        <th scope="col" class="text-center lh-1 table-success">線上<br class="d-block d-xl-none">數量</th>
                        <th scope="col" class="text-center lh-1 table-warning">線下<br class="d-block d-xl-none">營業額</th>
                        <th scope="col" class="text-center lh-1 table-warning">線下<br class="d-block d-xl-none">毛利</th>
                        <th scope="col" class="text-center lh-1 table-warning">線下<br class="d-block d-xl-none">數量</th>
                        <th scope="col" class="text-center">總營業額</th>
                        <th scope="col" class="text-center">總毛利</th>
                        <th scope="col" class="text-center">總數</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $on_price = 0;
                        $on_gross_profit = 0;
                        $on_qty = 0;
                        
                        $off_price = 0;
                        $off_gross_profit = 0;
                        $off_qty = 0;
                        $total_gross_profit = 0;
                        $total_price = 0;
                        $total_qty = 0;
                        
                    @endphp
                    @foreach ($dataList as $key => $data)
                        @php
                            $on_price += $data->on_price;
                            $on_gross_profit += $data->on_gross_profit;
                            $on_qty += $data->on_qty;
                            $off_price += $data->off_price;
                            $off_gross_profit += $data->off_gross_profit;
                            $off_qty += $data->off_qty;
                            $total_gross_profit += $data->total_gross_profit;
                            $total_price += $data->total_price;
                            $total_qty += $data->total_qty;
                            // $users += $data->users;
                            unset($query['user_id']);
                        @endphp
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td class="wrap">
                                <div>
                                    <a href="{{ Route('cms.product.edit', ['id' => $data->product_id], true) }}">
                                        {{ $data->product_title }}
                                    </a>
                                </div>
                                <div class="lh-1 small">
                                    <a
                                        href="{{ Route('cms.product.edit-price', ['id' => $data->product_id, 'sid' => $data->style_id]) }}">
                                        <span class="badge bg-secondary">
                                            {{ $data->style_title }}
                                        </span>

                                    </a>
                                </div>
                            </td>
                            <td>
                                <x-b-number :val="$data->on_price" prefix="$" />
                            </td>
                            <td>
                                <x-b-number :val="$data->on_gross_profit" prefix="$" />
                            </td>
                            <td>
                                <x-b-number :val="$data->on_qty" />
                            </td>
                            <td>
                                <x-b-number :val="$data->off_price" prefix="$" />
                            </td>
                            <td>
                                <x-b-number :val="$data->off_gross_profit" prefix="$" />
                            </td>
                            <td>
                                <x-b-number :val="$data->off_qty" />
                            </td>
                            <td>
                                <x-b-number :val="$data->total_price" prefix="$" />
                            </td>
                            <td>
                                <x-b-number :val="$data->total_gross_profit" prefix="$" />
                            </td>
                            <td>
                                <x-b-number :val="$data->total_qty" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2">合計</th>
                        <th>
                            <x-b-number :val="$on_price" prefix="$" />
                        </th>
                        <th>
                            <x-b-number :val="$on_gross_profit" prefix="$" />
                        </th>
                        <th>
                            <x-b-number :val="$on_qty"  />
                        </th>
                        <th>
                            <x-b-number :val="$off_price" prefix="$" />
                        </th>
                        <th>
                            <x-b-number :val="$off_gross_profit" prefix="$" />
                        </th>
                        <th>
                            <x-b-number :val="$off_qty" />
                        </th>
                        <th>
                            <x-b-number :val="$total_price" prefix="$" />
                        </th>
                        <th>
                            <x-b-number :val="$total_gross_profit" prefix="$" />
                        </th>
                        <th>
                            <x-b-number :val="$total_qty" />
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="col-auto">
        @if (isset($prevPage))
            <a href="{{ $prevPage }}" class="btn btn-outline-primary px-4" role="button">
                返回上一頁
            </a>
        @endif
    </div>
@endsection
@once
    @push('sub-styles')
        <style>
            h4 {
                color: #415583;
            }

            .negative::before {
                content: '-';
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script></script>
    @endpush
@endOnce
