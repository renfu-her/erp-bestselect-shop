@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">季報表</h2>

    <form method="GET">
        <div class="card shadow p-4 mb-4">
            <div class="row">
                <div class="col-auto">
                    <select class="form-select" name="y" aria-label="年度" placeholder="請選擇年度">
                        @foreach ($year_range as $value)
                            <option value="{{ $value }}" {{ $value == $cond['year'] ? 'selected' : '' }}>
                                {{ $value }}年</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-auto">
                    <select class="form-select" name="quarter" aria-label="季" data-placeholder="季度">
                        @for ($i = 1; $i <= 4; $i++)
                            <option value="{{ $i }}" {{ $i == $cond['quarter'] ? 'selected' : '' }}>
                                {{ $i }}季</option>
                        @endfor
                    </select>
                </div>

                <div class="col-auto align-self-end">
                    <button type="submit" class="btn btn-primary px-4">查詢</button>
                </div>
            </div>

        </div>
    </form>
    
    <div class="card shadow p-4 mb-4">
        <div>
            <table class="table table-borderless">
                <tr>
                    <th>總商品數：{{ number_format($products) }}</th>
                    <th>總廠商數：{{ number_format($suppliers) }}</th>
                </tr>
            </table>
        </div>
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead class="small align-middle">
                    <tr>
                        <th scope="col">月份</th>
                        <th scope="col" class="text-end">總營業額</th>
                        <th scope="col" class="text-end">總毛利</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $total_gross_profit = 0;
                        $total_price = 0;
                        
                    @endphp
                    @foreach ($dataList as $key => $data)
                        @php

                            $total_gross_profit += $data->total_gross_profit;
                            $total_price += $data->total_price;
                          
                        @endphp
                        <tr>
                            <td> 
                                {{ $data->m }}月
                            </td>
                            <td class="text-end">
                                <x-b-number :val="$data->total_price" prefix="$" />
                            </td>
                            <td class="text-end">
                                <x-b-number :val="$data->total_gross_profit" prefix="$" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>合計</th>
                       
                        <th class="text-end">
                            <x-b-number :val="$total_price" prefix="$" />
                        </th>
                        <th class="text-end">
                            <x-b-number :val="$total_gross_profit" prefix="$" />
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="card shadow p-4 mb-4">
        <div>
            <table class="table table-borderless">
                <tr>
                    <th>總商品數：{{ number_format($products) }}</th>
                    <th>總廠商數：{{ number_format($suppliers) }}</th>
                </tr>
            </table>
        </div>
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead class="small align-middle">
                    <tr>
                        <th scope="col">產品名稱</th>
                        <th scope="col">類別</th>
                        <th scope="col" class="text-end">數量</th>
                        <th scope="col" class="text-end">營業額</th>
                        <th scope="col" class="text-end">毛利</th>
                       
                    </tr>
                </thead>
                <tbody>
                    @php
                        $total_gross_profit = 0;
                        $total_price = 0;
                        $total_qty = 0;
                    @endphp
                    @foreach ($product as $key => $data)
                        @php

                            $total_gross_profit += $data->gross_profit;
                            $total_price += $data->price;
                            $total_qty += $data->qty;
                          
                        @endphp
                        <tr>
                            <td> 
                                {{ $data->product_title }}
                            </td>
                            <td> 
                                {{ $data->category }}
                            </td>
                            <td class="text-end">
                                <x-b-number :val="$data->qty"/>
                            </td>
                            <td class="text-end">
                                <x-b-number :val="$data->price" prefix="$" />
                            </td>
                            <td class="text-end">
                                <x-b-number :val="$data->gross_profit" prefix="$" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2">合計</th>
                        <th class="text-end">
                            <x-b-number :val="$total_qty"  />
                        </th>
                        <th class="text-end">
                            <x-b-number :val="$total_price" prefix="$" />
                        </th>
                        <th class="text-end">
                            <x-b-number :val="$total_gross_profit" prefix="$" />
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
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
@endOnce
