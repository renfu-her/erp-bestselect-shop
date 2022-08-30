@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">{{ $pre_data->grade_code . ' ' . $pre_data->grade_name }}</h2>

    <div class="card shadow p-4 mb-4">
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList mb-1">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">日期</th>
                        <th scope="col">當日金額</th>
                        <th scope="col">累計金額</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                        $a_price = $pre_data->net_price;
                    @endphp
                    @foreach($data_list as $key => $value)
                        <tr>
                            <td>{{ date('Y/m/d', strtotime($value->closing_date)) }}</td>
                            <td>{{ number_format($value->net_price) }}</td>
                            <td>{{ number_format($a_price + $value->net_price) }}</td>
                        </tr>
                        @php
                            $a_price += $value->net_price;
                        @endphp
                    @endforeach
                </tbody>

                {{--
                <tfoot>
                    <tr>
                        <td>小計</td>
                        <td>{{ number_format($data_list->sum('net_price')) }}</td>
                        <td>{{ number_format($a_price) }}</td>
                    </tr>
                </tfoot>
                --}}
            </table>
        </div>
    </div>
    <div class="col-auto">
        <a href="{{ url()->previous() }}" class="btn btn-outline-primary px-4" role="button">
            返回上一頁
        </a>
    </div>
@endsection

@once
    @push('sub-styles')
        <style>

        </style>
    @endpush

    @push('sub-scripts')
        <script>

        </script>
    @endpush
@endonce