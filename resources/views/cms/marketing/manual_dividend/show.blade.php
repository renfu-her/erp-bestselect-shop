@extends('layouts.main')
@section('sub-content')
    <div class="card p-4 mb-4">
        <div class="row">
            <div class="col-12 col-md-6 mb-3">
                <h6 class="m-0">
                    操作者：{{ $data->user_name ?? '' }}
                </h6>
            </div>
            <div class="col-12 col-md-6 mb-3">
                <h6 class="m-0">
                    日期：{{ date('Y/m/d H:i:s', strtotime($data->created_at)) }}
                </h6>
            </div>
            <div class="col-12 col-md-6 mb-3">
                <h6 class="m-0">
                    會計科目：{{ $data->category_title ?? '' }}
                </h6>
            </div>
            <div class="col-12 col-md-6 mb-3">
                <h6 class="m-0">
                    備註：{{ $data->note ?? '' }}
                </h6>
            </div>
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-sm table-striped tableList">
                <thead>
                    <tr>
                        <th scope="col" style="width:10%">#</th>
                        <th scope="col">帳號</th>
                        <th scope="col">點數</th>
                        <th scope="col">狀態</th>
                        <th scope="col">備註</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($log ?? [] as $key => $value)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>
                                {{ $value->account }}
                            </td>
                            <td>{{ number_format($value->dividend) }}</td>
                            <td @class(['text-success' => $value->status == '1',
                                'text-danger' => $value->status != '1'])>
                                @if ($value->status == '1')
                                    成功
                                @else
                                    失敗
                                @endif
                            </td>
                            <td>{{ $value->note }}</td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-auto">
        <a href="{{ Route('cms.manual-dividend.index', [], true) }}" class="btn btn-outline-primary px-4"
            role="button">返回列表</a>
    </div>
@endsection
