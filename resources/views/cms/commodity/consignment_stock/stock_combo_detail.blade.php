@extends('layouts.main')
@section('sub-content')
    <span class="badge bg-primary mb-2"><h4 class="mb-0">被組合數量</h4></span>
    <h2 class="mb-4">{{$title}} ({{ $style->sku }})</h2>

    <div class="card shadow p-4 mb-4">
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList mb-1 small">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">SKU</th>
                        <th scope="col">組合包名稱</th>
                        <th scope="col">款式</th>
                        <th scope="col">內含元素數量</th>
                        <th scope="col">可售數量</th>
                        <th scope="col">被組合數量</th>
                    </tr>
                </thead>
                <tbody>
                @php
                    $sum_of_qty = 0;
                @endphp
                 @foreach ($dataList as $key =>$data)
                     @php
                         $sum_of_qty += $data->total_stock;
                     @endphp
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td><a href="{{ Route('cms.stock.index', ['keyword' => $data->sku], true) }}">{{$data->sku}}</a></td>
                        <td>{{$data->title}}</td>
                        <td>{{$data->spec}}</td>
                        <td>{{$data->qty}}</td>
                        <td>{{$data->in_stock}}</td>
                        <td>{{$data->total_stock}}</td>
                    </tr>
                 @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <th>被組合總數量</th>
                        <td class="text-left">{{ $sum_of_qty }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="row flex-column-reverse flex-sm-row">
        <div class="col-auto">
            <a href="{{ $returnAction }}" class="btn btn-outline-primary px-4"
                role="button">返回列表</a>
        </div>
    </div>

@endsection
@once
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endonce
