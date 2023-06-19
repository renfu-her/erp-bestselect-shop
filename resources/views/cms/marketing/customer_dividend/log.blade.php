@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">點數提領紀錄</h2>
    <div class="card shadow p-4 mb-4">
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList mb-1">
                <thead class="">
                    <tr>
                        <th scope="col" style="width:10px">#</th>
                        <th scope="col">姓名</th>
                        <th scope="col">ERP帳號</th>
                        <th scope="col">提領點數</th>
                        <th scope="col">時間</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>{{ $data->name }}</td>
                            <td>{{ $data->account }}</td>
                            <td>{{ number_format($data->point) }}</td>
                            <td class="wrap lh-sm">{{ $data->created_at ? date('Y/m/d H:i:s', strtotime($data->created_at)) : '-' }}</td>
                        </tr>               
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-auto">
        <a href="{{ route('cms.user-dividend.index') }}" class="btn btn-outline-primary px-4">
            返回列表
        </a>
    </div>

    <!-- Modal -->
@endsection

@once
    @push('sub-scripts')
    @endpush
@endonce
