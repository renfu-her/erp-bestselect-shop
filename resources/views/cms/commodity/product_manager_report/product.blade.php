@extends('layouts.main')
@section('sub-content')
    <div class="card shadow p-4 mb-4">
        <h4>
            {{ $pageTitle }}
        </h4>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead class="small">
                    <tr>
                        <th scope="col" style="width:40px">#</th>
                        <th scope="col">
                            商品
                        </th>
                        <th scope="col">
                            款式
                        </th>
                        <th scope="col" class="text-center">線上營業額</th>
                        <th scope="col" class="text-center">線上毛利</th>
                        <th scope="col" class="text-center">線上數量</th>
                        <th scope="col" class="text-center">線下營業額</th>
                        <th scope="col" class="text-center">線下毛利</th>
                        <th scope="col" class="text-center">線下數量</th>
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
                            <td>
                                <a href="{{ Route('cms.product.edit', ['id' => $data->product_id], true) }}">
                                    {{ $data->product_title }}
                                </a>
                            </td>
                            <td>
                                <a
                                    href="{{ Route('cms.product.edit-price', ['id' => $data->product_id, 'sid' => $data->style_id]) }}">{{ $data->style_title }}</a>
                            </td>
                            <td class="text-center">{{ $data->on_price }}</td>
                            <td class="text-center">{{ $data->on_gross_profit }}</td>
                            <td class="text-center">{{ $data->on_qty }}</td>
                            <td class="text-center">{{ $data->off_price }}</td>
                            <td class="text-center">{{ $data->off_gross_profit }}</td>
                            <td class="text-center">{{ $data->off_qty }}</td>
                            <td class="text-center">{{ $data->total_price }}</td>
                            <td class="text-center">{{ $data->total_gross_profit }}</td>
                            <td class="text-center">{{ $data->total_qty }}</td>

                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">合計</th>
                        <th class="text-center">{{ $on_price }}</th>
                        <th class="text-center">{{ $on_gross_profit }}</th>
                        <th class="text-center">{{ $on_qty }}</th>
                        <th class="text-center">{{ $off_price }}</th>
                        <th class="text-center">{{ $off_gross_profit }}</th>
                        <th class="text-center">{{ $off_qty }}</th>
                        <th class="text-center">{{ $total_price }}</th>
                        <th class="text-center">{{ $total_gross_profit }}</th>
                        <th class="text-center">{{ $total_qty }}</th>
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
        </style>
    @endpush
    @push('sub-scripts')
        <script></script>
    @endpush
@endOnce
