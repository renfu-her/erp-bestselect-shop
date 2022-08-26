@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">{{ date('Y/m/d', strtotime($day_end->deo_closing_date)) }} 日結清單</h2>
    
    <div class="card shadow p-4 mb-4">
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList mb-1">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">編號</th>
                        <th scope="col">傳票編號</th>
                        <th scope="col">單據編號</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                        $data_list = $day_end->deo_items;
                    @endphp
                    @foreach($data_list as $key => $value)
                        <tr>
                            <th>{{ $key + 1 }}</th>
                            <td>{{ $value->sn }}</td>
                            <td><a href="{{ $value->link }}">{{ $value->source_sn }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
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