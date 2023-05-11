@extends('layouts.main')
@section('sub-content')
    <form method="GET">
        <div class="card shadow p-4 mb-4">
            <div class="row mb-3">
                <div class="col-auto">
                    <label class="form-label">年度</label>
                    <select class="form-select" name="y" aria-label="年度" placeholder="請選擇年度">
                        @foreach ($year_range as $value)
                            <option value="{{ $value }}" {{ $value == $cond['year'] ? 'selected' : '' }}>
                                {{ $value }}年</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-auto">
                    <label class="form-label">季</label>
                    <select class="form-select" name="quarter" aria-label="季" data-placeholder="季度">
                        @for ($i = 1; $i <= 4; $i++)
                            <option value="{{ $i }}" {{ $value == $cond['quarter'] ? 'selected' : '' }}>
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
    總商品:{{ $products }}
    總廠商:{{ $suppliers }}
    <div class="card shadow p-4 mb-4">
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead class="small align-middle">
                    <tr>
            
                        <th scope="col">月份</th>
                        <th scope="col" class="text-center">總營業額</th>
                        <th scope="col" class="text-center">總毛利</th>
                        <!-- <th scope="col" class="text-center">人數</th>-->
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
